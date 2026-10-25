import { apiRequest, ApiClientError } from '@/lib/api/client';
import type { ApiDataResponse } from '@/lib/api/endpoints';
import { getStoredToken } from '@/lib/auth/token';
import { API_BASE_URL } from '@/lib/constants';
import type { EntityType } from '@/components/ui/badge';
import {
  parseSseChunk,
  type AiStreamCallbacks,
  type AiStreamCitation,
  type AiStreamEvent,
} from '@/components/ai/ai-streaming';

export interface AiCitation {
  entity_uuid: string;
  entity_type: EntityType;
  title: string;
  excerpt?: string | null;
  source_uuid?: string | null;
  confidence?: number;
  slug?: string | null;
}

export interface AiRelatedEntity {
  uuid: string;
  type: EntityType;
  title: string;
}

export interface AiChatRequest {
  message: string;
  locale: string;
  conversation_uuid?: string | null;
  context_entity_uuid?: string | null;
}

export interface AiChatResponse {
  conversation_uuid: string;
  message_uuid: string;
  answer: string;
  citations: AiCitation[];
  disclaimer: string;
  related_entities?: AiRelatedEntity[];
}

export interface AiRecommendation {
  uuid: string;
  type: EntityType;
  title: string;
  summary?: string | null;
  reason?: string | null;
}

export function chat(payload: AiChatRequest) {
  return apiRequest<ApiDataResponse<AiChatResponse>>('/ai/chat', {
    method: 'POST',
    body: JSON.stringify(payload),
    locale: payload.locale,
  });
}

export function getRecommendations(
  locale: string,
  options?: { entity_uuid?: string; limit?: number },
) {
  const params = new URLSearchParams();
  if (options?.entity_uuid) params.set('entity_uuid', options.entity_uuid);
  if (options?.limit) params.set('limit', String(options.limit));
  const query = params.toString();

  return apiRequest<ApiDataResponse<AiRecommendation[]>>(
    `/${locale}/ai/recommendations${query ? `?${query}` : ''}`,
    { locale },
  );
}

export type { AiStreamCallbacks, AiStreamCitation, AiStreamEvent };

/**
 * Streams AI chat via SSE (`Accept: text/event-stream`).
 * Parses `delta`, `citation`, and `done` event payloads.
 */
export async function chatStream(
  payload: AiChatRequest,
  callbacks: AiStreamCallbacks,
  signal?: AbortSignal,
): Promise<void> {
  const token = getStoredToken();
  const response = await fetch(`${API_BASE_URL}/ai/chat`, {
    method: 'POST',
    headers: {
      Accept: 'text/event-stream',
      'Content-Type': 'application/json',
      'Accept-Language': payload.locale,
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
    },
    body: JSON.stringify(payload),
    credentials: 'include',
    signal,
  });

  if (!response.ok) {
    let message = response.statusText;
    let code = 'HTTP_ERROR';
    try {
      const body = (await response.json()) as {
        error?: { code?: string; message?: string };
      };
      code = body.error?.code ?? code;
      message = body.error?.message ?? message;
    } catch {
      // keep defaults
    }
    throw new ApiClientError(code, message, response.status);
  }

  if (!response.body) {
    throw new ApiClientError('AI_STREAM_ERROR', 'Empty stream body', response.status);
  }

  const reader = response.body.getReader();
  const decoder = new TextDecoder();
  let buffer = '';

  try {
    while (true) {
      const { done, value } = await reader.read();
      if (done) break;

      buffer += decoder.decode(value, { stream: true });
      const parts = buffer.split('\n\n');
      buffer = parts.pop() ?? '';

      for (const part of parts) {
        const events = parseSseChunk(part);
        for (const event of events) {
          dispatchStreamEvent(event, callbacks);
        }
      }
    }

    if (buffer.trim()) {
      const events = parseSseChunk(buffer);
      for (const event of events) {
        dispatchStreamEvent(event, callbacks);
      }
    }
  } finally {
    reader.releaseLock();
  }
}

function dispatchStreamEvent(event: AiStreamEvent, callbacks: AiStreamCallbacks) {
  switch (event.type) {
    case 'delta':
      callbacks.onDelta?.(event.text);
      break;
    case 'citation':
      callbacks.onCitation?.(event.citation);
      break;
    case 'done':
      callbacks.onDone?.(event);
      break;
    case 'error':
      callbacks.onError?.(event.message);
      break;
    default:
      break;
  }
}
