'use client';

import { useLocale } from 'next-intl';
import { Link } from '@/i18n/navigation';
import type { QuranVerseSummary } from '@/lib/api/quran';
import { cn } from '@/lib/utils';

export interface VerseBlockProps {
  verse: QuranVerseSummary;
  showReference?: boolean;
  className?: string;
}

export function VerseBlock({ verse, showReference = true, className }: VerseBlockProps) {
  const locale = useLocale();
  const reference = verse.reference ?? `${verse.surah}:${verse.ayah}`;

  return (
    <article
      className={cn(
        'flex flex-col gap-3 rounded-lg border border-[var(--color-border-default)] bg-[var(--color-wisdom-subtle)] p-4',
        className,
      )}
      lang="ar"
      dir="rtl"
    >
      {showReference ? (
        <div className="flex items-center justify-between gap-2" dir={locale === 'ar' ? 'rtl' : 'ltr'}>
          <Link
            href={`/quran/verses/${reference}`}
            className="text-sm font-medium text-[var(--color-wisdom)] hover:underline"
          >
            {reference}
          </Link>
        </div>
      ) : null}
      <p
        className="text-2xl leading-loose text-[var(--color-ink)]"
        style={{ fontFamily: 'var(--font-arabic-ui)' }}
      >
        {verse.text_uthmani}
      </p>
      {verse.text_simple && verse.text_simple !== verse.text_uthmani ? (
        <p className="text-lg leading-relaxed text-[var(--color-ink-secondary)]">{verse.text_simple}</p>
      ) : null}
    </article>
  );
}
