'use client';

import { useTranslations } from 'next-intl';
import { Link } from '@/i18n/navigation';
import type { AiChatMessage } from '@/hooks/use-ai-chat';
import type { AiCitation, AiRelatedEntity } from '@/lib/api/ai';
import type { EntityType } from '@/components/ui/badge';
import { entityPublicHref } from '@/lib/entity/types';
import { cn } from '@/lib/utils';

export function AiMessageList({
  messages,
  streamingText,
  isStreaming,
}: {
  messages: AiChatMessage[];
  streamingText?: string;
  isStreaming?: boolean;
}) {
  const t = useTranslations('ai');

  return (
    <div className="flex flex-col gap-3" role="log" aria-relevant="additions">
      {messages.length === 0 && !isStreaming ? (
        <p className="text-center text-sm text-[var(--color-ink-tertiary)]">{t('emptyConversation')}</p>
      ) : null}

      {messages.map((message) => (
        <MessageBubble key={message.id} message={message} />
      ))}

      {isStreaming ? (
        <div
          className="max-w-[90%] self-start rounded-lg border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] px-3 py-2 text-sm text-[var(--color-ink)]"
          aria-live="polite"
          aria-busy="true"
        >
          {streamingText || t('thinking')}
          <span className="ms-1 inline-block h-3 w-1 animate-pulse bg-[var(--color-scholar)] align-middle" />
        </div>
      ) : null}
    </div>
  );
}

function MessageBubble({ message }: { message: AiChatMessage }) {
  const t = useTranslations('ai');
  const isUser = message.role === 'user';

  return (
    <div
      className={cn(
        'flex max-w-[90%] flex-col gap-2 text-sm',
        isUser ? 'self-end' : 'self-start',
      )}
    >
      <div
        className={cn(
          'rounded-lg px-3 py-2',
          isUser
            ? 'bg-[var(--color-scholar-subtle)] text-[var(--color-ink)]'
            : 'border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] text-[var(--color-ink)]',
        )}
      >
        <p className="whitespace-pre-wrap leading-relaxed">{message.content}</p>
      </div>

      {message.citations && message.citations.length > 0 ? (
        <div className="flex flex-wrap gap-1.5" aria-label={t('citations')}>
          {message.citations.map((citation, index) => (
            <CitationChip key={`${citation.entity_uuid}-${index}`} citation={citation} />
          ))}
        </div>
      ) : null}

      {message.relatedEntities && message.relatedEntities.length > 0 ? (
        <div className="flex flex-col gap-1">
          <p className="text-xs font-medium text-[var(--color-ink-secondary)]">{t('relatedTopics')}</p>
          <div className="flex flex-wrap gap-1.5">
            {message.relatedEntities.map((entity) => (
              <RelatedChip key={entity.uuid} entity={entity} />
            ))}
          </div>
        </div>
      ) : null}
    </div>
  );
}

function CitationChip({ citation }: { citation: AiCitation }) {
  const href = entityPublicHref(citation.entity_type, citation.entity_uuid, citation.slug);

  return (
    <Link
      href={href}
      className="inline-flex max-w-full items-center rounded-full bg-[var(--color-scholar-subtle)] px-2.5 py-1 text-xs font-medium text-[var(--color-scholar)] transition-colors hover:bg-[color-mix(in_srgb,var(--color-scholar)_18%,transparent)]"
      title={citation.excerpt ?? citation.title}
    >
      <span className="truncate">{citation.title}</span>
    </Link>
  );
}

function RelatedChip({ entity }: { entity: AiRelatedEntity }) {
  const href = entityPublicHref(entity.type as EntityType, entity.uuid);

  return (
    <Link
      href={href}
      className="inline-flex rounded-full border border-[var(--color-border-default)] px-2.5 py-1 text-xs text-[var(--color-ink-secondary)] hover:border-[var(--color-scholar)] hover:text-[var(--color-scholar)]"
    >
      {entity.title}
    </Link>
  );
}
