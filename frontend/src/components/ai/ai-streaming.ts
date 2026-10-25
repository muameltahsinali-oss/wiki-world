export interface AiStreamCitation {
  entity_uuid: string;
  entity_type: string;
  title: string;
  excerpt?: string | null;
  source_uuid?: string | null;
  confidence?: number;
}

export type AiStreamEvent =
  | { type: 'delta'; text: string }
  | { type: 'citation'; citation: AiStreamCitation }
  | {
      type: 'done';
      conversation_uuid?: string;
      message_uuid?: string;
      disclaimer?: string;
      related_entities?: Array<{ uuid: string; type: string; title: string }>;
    }
  | { type: 'error'; message: string };

export interface AiStreamCallbacks {
  onDelta?: (text: string) => void;
  onCitation?: (citation: AiStreamCitation) => void;
  onDone?: (event: Extract<AiStreamEvent, { type: 'done' }>) => void;
  onError?: (message: string) => void;
}

/**
 * Parse a single SSE frame (block separated by blank lines).
 * Supports `event:` + `data:` lines and bare `data:` JSON with a `type` field.
 */
export function parseSseChunk(chunk: string): AiStreamEvent[] {
  const lines = chunk.split(/\r?\n/);
  let eventName = '';
  const dataLines: string[] = [];

  for (const line of lines) {
    if (!line || line.startsWith(':')) continue;
    if (line.startsWith('event:')) {
      eventName = line.slice(6).trim();
      continue;
    }
    if (line.startsWith('data:')) {
      dataLines.push(line.slice(5).trimStart());
    }
  }

  if (dataLines.length === 0) return [];

  const raw = dataLines.join('\n');
  if (raw === '[DONE]') {
    return [{ type: 'done' }];
  }

  let payload: unknown;
  try {
    payload = JSON.parse(raw);
  } catch {
    if (eventName === 'delta' || !eventName) {
      return [{ type: 'delta', text: raw }];
    }
    return [];
  }

  return [normalizeStreamPayload(eventName, payload)];
}

function normalizeStreamPayload(eventName: string, payload: unknown): AiStreamEvent {
  if (typeof payload === 'string') {
    return { type: 'delta', text: payload };
  }

  if (!payload || typeof payload !== 'object') {
    return { type: 'error', message: 'Invalid stream payload' };
  }

  const data = payload as Record<string, unknown>;
  const type = (eventName || (typeof data.type === 'string' ? data.type : '')).toLowerCase();

  if (type === 'delta' || type === 'message' || type === 'token') {
    const text =
      typeof data.text === 'string'
        ? data.text
        : typeof data.delta === 'string'
          ? data.delta
          : typeof data.content === 'string'
            ? data.content
            : '';
    return { type: 'delta', text };
  }

  if (type === 'citation') {
    const citation = (data.citation ?? data) as AiStreamCitation;
    return {
      type: 'citation',
      citation: {
        entity_uuid: String(citation.entity_uuid ?? ''),
        entity_type: String(citation.entity_type ?? 'article'),
        title: String(citation.title ?? ''),
        excerpt: citation.excerpt ?? null,
        source_uuid: citation.source_uuid ?? null,
        confidence: citation.confidence,
      },
    };
  }

  if (type === 'done' || type === 'complete') {
    return {
      type: 'done',
      conversation_uuid:
        typeof data.conversation_uuid === 'string' ? data.conversation_uuid : undefined,
      message_uuid: typeof data.message_uuid === 'string' ? data.message_uuid : undefined,
      disclaimer: typeof data.disclaimer === 'string' ? data.disclaimer : undefined,
      related_entities: Array.isArray(data.related_entities)
        ? (data.related_entities as Array<{ uuid: string; type: string; title: string }>)
        : undefined,
    };
  }

  if (type === 'error') {
    return {
      type: 'error',
      message: typeof data.message === 'string' ? data.message : 'Stream error',
    };
  }

  if (typeof data.text === 'string' || typeof data.delta === 'string') {
    return {
      type: 'delta',
      text: String(data.text ?? data.delta ?? ''),
    };
  }

  return { type: 'error', message: 'Unknown stream event' };
}
