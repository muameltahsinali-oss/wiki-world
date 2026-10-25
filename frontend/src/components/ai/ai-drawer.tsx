'use client';

import { useEffect, useState } from 'react';
import { useTranslations } from 'next-intl';
import { Send } from 'lucide-react';
import { AiDisclaimer, hasAcknowledgedAiDisclaimer } from '@/components/ai/ai-disclaimer';
import { AiMessageList } from '@/components/ai/ai-message-list';
import { Button } from '@/components/ui/button';
import { Drawer } from '@/components/ui/drawer';
import { useAiChat } from '@/hooks/use-ai-chat';
import { cn } from '@/lib/utils';

export function AiDrawer({
  open,
  onOpenChange,
  contextEntityUuid,
}: {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  contextEntityUuid?: string | null;
}) {
  const t = useTranslations('ai');
  const [ackReady, setAckReady] = useState(false);
  const [acknowledged, setAcknowledged] = useState(false);
  const [input, setInput] = useState('');
  const { messages, streamingText, isStreaming, error, sendMessage, stop } = useAiChat({
    contextEntityUuid,
  });

  useEffect(() => {
    if (!open) return;
    setAcknowledged(hasAcknowledgedAiDisclaimer());
    setAckReady(true);
  }, [open]);

  const canChat = acknowledged;

  const handleSubmit = async (event: React.FormEvent) => {
    event.preventDefault();
    if (!canChat || !input.trim()) return;
    const text = input;
    setInput('');
    await sendMessage(text);
  };

  return (
    <Drawer open={open} onOpenChange={onOpenChange} title={t('title')} className="w-[min(100%,28rem)]">
      <div className="flex h-full min-h-[min(70vh,32rem)] flex-col gap-4">
        {ackReady ? (
          <AiDisclaimer onAcknowledged={() => setAcknowledged(true)} />
        ) : null}

        <div className="flex-1 overflow-y-auto">
          <AiMessageList
            messages={messages}
            streamingText={streamingText}
            isStreaming={isStreaming}
          />
        </div>

        {error ? (
          <p className="text-sm text-[var(--color-error)]" role="alert">
            {error}
          </p>
        ) : null}

        <form onSubmit={handleSubmit} className="flex flex-col gap-2 border-t border-[var(--color-border-default)] pt-3">
          <label htmlFor="ai-chat-input" className="sr-only">
            {t('inputLabel')}
          </label>
          <textarea
            id="ai-chat-input"
            value={input}
            onChange={(event) => setInput(event.target.value)}
            placeholder={t('inputPlaceholder')}
            disabled={!canChat || isStreaming}
            rows={2}
            className={cn(
              'w-full resize-none rounded-md border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] px-3 py-2 text-sm text-[var(--color-ink)] placeholder:text-[var(--color-ink-tertiary)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[var(--color-scholar)] disabled:opacity-50',
            )}
          />
          <div className="flex items-center justify-end gap-2">
            {isStreaming ? (
              <Button type="button" variant="ghost" size="sm" onClick={stop}>
                {t('stop')}
              </Button>
            ) : null}
            <Button type="submit" size="sm" disabled={!canChat || isStreaming || !input.trim()}>
              <Send className="h-4 w-4" aria-hidden />
              {t('send')}
            </Button>
          </div>
        </form>
      </div>
    </Drawer>
  );
}
