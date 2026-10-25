import { getLocale, getTranslations } from 'next-intl/server';
import { Link } from '@/i18n/navigation';
import { Button } from '@/components/ui/button';

export default async function NotFoundPage() {
  const t = await getTranslations('common');
  const errors = await getTranslations('errors');
  const locale = await getLocale();

  return (
    <main className="mx-auto flex min-h-[60vh] max-w-lg flex-col items-center justify-center gap-4 px-6 text-center">
      <h1 className="text-2xl font-semibold text-[var(--color-ink)]">{t('notFound')}</h1>
      <p className="text-sm text-[var(--color-ink-secondary)]">{errors('notFoundDescription')}</p>
      <Link href="/" locale={locale}>
        <Button variant="secondary">{t('backHome')}</Button>
      </Link>
    </main>
  );
}
