import { getTranslations } from 'next-intl/server';
import { Alert } from '@/components/ui/alert';
import { Link } from '@/i18n/navigation';

export default async function MaintenancePage() {
  const t = await getTranslations('maintenance');
  const common = await getTranslations('common');

  return (
    <main className="mx-auto flex min-h-[60vh] w-full max-w-lg flex-col items-center justify-center gap-6 px-4 py-16 text-center sm:px-6">
      <h1 className="text-3xl font-semibold text-[var(--color-ink)]">{t('title')}</h1>
      <Alert variant="warning" title={t('title')}>
        {t('description')}
      </Alert>
      <p className="text-sm text-[var(--color-ink-secondary)]">{t('checkBack')}</p>
      <Link href="/" className="text-sm text-[var(--color-scholar)] hover:underline">
        {common('backHome')}
      </Link>
    </main>
  );
}
