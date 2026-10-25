'use client';

import { useCallback, useRef, useState } from 'react';
import { useLocale } from 'next-intl';
import { chatStream, type AiCitation, type AiRelatedEntity } from '@/lib/api/ai';
import type { EntityType } from '@/components/ui/badge';
import { ApiClientError } from '@/lib/api/client';

export interface AiChatMessage {
  id: string;
  role: 'user' | 'assistant' | 'system';
  content: string;
  citations?: AiCitation[];
  relatedEntities?: AiRelatedEntity[];
  streaming?: boolean;
}

export function useAiChat(options?: { contextEntityUuid?: string | null }) {
  const locale = useLocale();
  const [messages, setMessages] = useState<AiChatMessage[]>([]);
  const [streamingText, setStreamingText] = useState('');
  const [isStreaming, setIsStreaming] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [conversationUuid, setConversationUuid] = useState<string | null>(null);
  const abortRef = useRef<AbortController | null>(null);
  const citationsRef = useRef<AiCitation[]>([]);

  const reset = useCallback(() => {
    abortRef.current?.abort();
    abortRef.current = null;
    setMessages([]);
    setStreamingText('');
    setIsStreaming(false);
    setError(null);
    setConversationUuid(null);
    citationsRef.current = [];
  }, []);

  const sendMessage = useCallback(
    async (text: string) => {
      const trimmed = text.trim();
      if (!trimmed || isStreaming) return;

      setError(null);
      const userMessage: AiChatMessage = {
        id: `user-${Date.now()}`,
        role: 'user',
        content: trimmed,
      };

      setMessages((prev) => [...prev, userMessage]);
      setIsStreaming(true);
      setStreamingText('');
      citationsRef.current = [];

      const controller = new AbortController();
      abortRef.current = controller;

      try {
        await chatStream(
          {
            message: trimmed,
            locale,
            conversation_uuid: conversationUuid,
            context_entity_uuid: options?.contextEntityUuid ?? null,
          },
          {
            onDelta: (delta) => {
              setStreamingText((prev) => prev + delta);
            },
            onCitation: (citation) => {
              citationsRef.current = [
                ...citationsRef.current,
                {
                  entity_uuid: citation.entity_uuid,
                  entity_type: citation.entity_type as EntityType,
                  title: citation.title,
                  excerpt: citation.excerpt,
                  source_uuid: citation.source_uuid,
                  confidence: citation.confidence,
                },
              ];
            },
                            onDone: (event) => {
              if (event.conversation_uuid) {
                setConversationUuid(event.conversation_uuid);
              }

              const related =
                event.related_entities?.map((item) => ({
                  uuid: item.uuid,
                  type: item.type as EntityType,
                  title: item.title,
                })) ?? [];

              setStreamingText((current) => {
                const finalText = current;
                setMessages((prev) => [
                  ...prev,
                  {
                    id: event.message_uuid ?? `assistant-${Date.now()}`,
                    role: 'assistant',
                    content: finalText,
                    citations: [...citationsRef.current],
                    relatedEntities: related,
                  },
                ]);
                return '';
              });
            },
            onError: (message) => {
              setError(message);
            },
          },
          controller.signal,
        );
      } catch (err) {
        if (err instanceof DOMException && err.name === 'AbortError') {
          return;
        }
        const message =
          err instanceof ApiClientError
            ? err.message
            : err instanceof Error
              ? err.message
              : 'Request failed';
        setError(message);
        setStreamingText((current) => {
          if (current) {
            setMessages((prev) => [
              ...prev,
              {
                id: `assistant-${Date.now()}`,
                role: 'assistant',
                content: current,
                citations: [...citationsRef.current],
              },
            ]);
          }
          return '';
        });
      } finally {
        setIsStreaming(false);
        abortRef.current = null;
      }
    },
    [conversationUuid, isStreaming, locale, options?.contextEntityUuid],
  );

  const stop = useCallback(() => {
    abortRef.current?.abort();
    abortRef.current = null;
    setIsStreaming(false);
  }, []);

  return {
    messages,
    streamingText,
    isStreaming,
    error,
    conversationUuid,
    sendMessage,
    stop,
    reset,
  };
}
