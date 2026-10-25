'use client';

import { useEffect, useState } from 'react';
import { useTranslations } from 'next-intl';
import { Alert } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';

const STORAGE_KEY = 'wikiworld-ai-disclaimer-ack';

export function hasAcknowledgedAiDisclaimer(): boolean {
  if (typeof window === 'undefined') return false;
  try {
    return window.localStorage.getItem(STORAGE_KEY) === '1';
  } catch {
    return false;
  }
}

export function acknowledgeAiDisclaimer(): void {
  try {
    window.localStorage.setItem(STORAGE_KEY, '1');
  } catch {
    // ignore quota / private mode
  }
}

export function AiDisclaimer({
  onAcknowledged,
}: {
  onAcknowledged?: () => void;
}) {
  const t = useTranslations('ai');
  const [acknowledged, setAcknowledged] = useState(false);
  const [ready, setReady] = useState(false);

  useEffect(() => {
    setAcknowledged(hasAcknowledgedAiDisclaimer());
    setReady(true);
  }, []);

  if (!ready) return null;

  if (acknowledged) {
    return (
      <p className="rounded-md border border-[var(--color-border-default)] bg-[var(--color-surface-sunken)] px-3 py-2 text-xs text-[var(--color-ink-secondary)]">
        {t('disclaimerShort')}
      </p>
    );
  }

  return (
    <div className="flex flex-col gap-3 rounded-lg border border-[var(--color-border-default)] bg-[var(--color-surface-sunken)] p-4">
      <Alert variant="warning" title={t('disclaimerTitle')}>
        <p className="text-sm leading-relaxed">{t('disclaimerBody')}</p>
      </Alert>
      <Button
        type="button"
        onClick={() => {
          acknowledgeAiDisclaimer();
          setAcknowledged(true);
          onAcknowledged?.();
        }}
      >
        {t('disclaimerAcknowledge')}
      </Button>
    </div>
  );
}
