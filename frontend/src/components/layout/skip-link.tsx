import { getTranslations } from 'next-intl/server';
import { Link } from '@/i18n/navigation';

export async function SkipLink() {
  const t = await getTranslations('common');

  return (
    <Link
      href="#main-content"
      className="sr-only focus:not-sr-only focus:absolute focus:start-4 focus:top-4 focus:z-[300] focus:rounded-md focus:bg-[var(--color-scholar)] focus:px-4 focus:py-2 focus:text-white"
    >
      {t('skipToContent')}
    </Link>
  );
}
