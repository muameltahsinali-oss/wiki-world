'use client';

import { useLocale, useTranslations } from 'next-intl';
import { usePathname, useRouter } from '@/i18n/navigation';
import { routing, type Locale } from '@/i18n/routing';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export function LanguageToggle({ className }: { className?: string }) {
  const t = useTranslations('nav');
  const locale = useLocale() as Locale;
  const pathname = usePathname();
  const router = useRouter();

  const otherLocale = routing.locales.find((item) => item !== locale) ?? routing.defaultLocale;

  return (
    <Button
      type="button"
      variant="ghost"
      size="sm"
      className={cn('font-medium', className)}
      onClick={() => router.replace(pathname, { locale: otherLocale })}
      aria-label={t('languageToggle')}
    >
      {otherLocale === 'ar' ? 'العربية' : 'English'}
    </Button>
  );
}
