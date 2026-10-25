import { getTranslations } from 'next-intl/server';
import { Link } from '@/i18n/navigation';

export async function Footer() {
  const t = await getTranslations('footer');
  const year = new Date().getFullYear();

  const links = [
    { href: '/about', label: t('about') },
    { href: '/privacy', label: t('privacy') },
    { href: '/terms', label: t('terms') },
    { href: '/contact', label: t('contact') },
  ] as const;

  return (
    <footer className="mt-auto border-t border-[var(--color-border-default)] bg-[var(--color-surface-sunken)]">
      <div className="mx-auto flex max-w-7xl flex-col gap-6 px-4 py-10 sm:px-6">
        <p className="max-w-2xl text-sm text-[var(--color-ink-secondary)]">{t('trustStatement')}</p>
        <nav className="flex flex-wrap gap-4" aria-label="Footer">
          {links.map((link) => (
            <Link
              key={link.href}
              href={link.href}
              className="text-sm text-[var(--color-ink-secondary)] transition-colors hover:text-[var(--color-scholar)]"
            >
              {link.label}
            </Link>
          ))}
        </nav>
        <p className="text-xs text-[var(--color-ink-tertiary)]">{t('copyright', { year })}</p>
      </div>
    </footer>
  );
}
