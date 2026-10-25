'use client';

import { Menu, Search, Sparkles, X } from 'lucide-react';
import { useTranslations } from 'next-intl';
import { useState } from 'react';
import { AiDrawer } from '@/components/ai/ai-drawer';
import { LanguageToggle } from '@/components/layout/language-toggle';
import { AuthNav } from '@/components/layout/auth-nav';
import { Button } from '@/components/ui/button';
import { ThemeToggle } from '@/components/ui/theme-toggle';
import { Link } from '@/i18n/navigation';
import { cn } from '@/lib/utils';

export function Header() {
  const t = useTranslations('nav');
  const app = useTranslations('app');
  const [mobileOpen, setMobileOpen] = useState(false);
  const [aiOpen, setAiOpen] = useState(false);

  const navItems = [
    { href: '/', label: t('home') },
    { href: '/quran', label: t('quran') },
    { href: '/ahlulbayt', label: t('ahlulbayt') },
    { href: '/search', label: t('search') },
  ] as const;

  return (
    <>
      <header className="sticky top-0 z-[var(--z-sticky,200)] border-b border-[var(--color-border-default)] bg-[var(--color-surface-raised)]/95 backdrop-blur">
        <div className="mx-auto flex h-[var(--header-height)] max-w-7xl items-center justify-between gap-4 px-4 sm:px-6">
          <div className="flex items-center gap-6">
            <Link href="/" className="text-base font-semibold text-[var(--color-scholar)]">
              {app('name')}
            </Link>
            <nav className="hidden items-center gap-1 md:flex" aria-label="Main">
              {navItems.map((item) => (
                <Link
                  key={item.href}
                  href={item.href}
                  className="rounded-md px-3 py-2 text-sm text-[var(--color-ink-secondary)] transition-colors hover:bg-[var(--color-surface-sunken)] hover:text-[var(--color-ink)]"
                >
                  {item.label}
                </Link>
              ))}
            </nav>
          </div>

          <div className="hidden items-center gap-2 md:flex">
            <Link href="/search">
              <Button type="button" variant="ghost" size="sm" aria-label={t('search')}>
                <Search className="h-4 w-4" />
              </Button>
            </Link>
            <Button
              type="button"
              variant="ghost"
              size="sm"
              aria-label={t('aiAssistant')}
              onClick={() => setAiOpen(true)}
            >
              <Sparkles className="h-4 w-4" />
            </Button>
            <LanguageToggle />
            <ThemeToggle />
            <AuthNav />
          </div>

          <div className="flex items-center gap-1 md:hidden">
            <Button
              type="button"
              variant="ghost"
              size="sm"
              aria-label={t('aiAssistant')}
              onClick={() => setAiOpen(true)}
            >
              <Sparkles className="h-4 w-4" />
            </Button>
            <Button
              type="button"
              variant="ghost"
              size="sm"
              onClick={() => setMobileOpen((open) => !open)}
              aria-expanded={mobileOpen}
              aria-label={mobileOpen ? t('closeMenu') : t('menu')}
            >
              {mobileOpen ? <X className="h-5 w-5" /> : <Menu className="h-5 w-5" />}
            </Button>
          </div>
        </div>

        <div
          className={cn(
            'border-t border-[var(--color-border-default)] bg-[var(--color-surface-raised)] md:hidden',
            mobileOpen ? 'block' : 'hidden',
          )}
        >
          <nav className="flex flex-col gap-1 p-4" aria-label="Mobile">
            {navItems.map((item) => (
              <Link
                key={item.href}
                href={item.href}
                className="rounded-md px-3 py-2 text-sm text-[var(--color-ink-secondary)] hover:bg-[var(--color-surface-sunken)]"
                onClick={() => setMobileOpen(false)}
              >
                {item.label}
              </Link>
            ))}
            <div className="mt-2 flex flex-col gap-2 border-t border-[var(--color-border-default)] pt-4">
              <div className="flex items-center gap-2">
                <LanguageToggle />
                <ThemeToggle />
              </div>
              <AuthNav />
            </div>
          </nav>
        </div>
      </header>

      <AiDrawer open={aiOpen} onOpenChange={setAiOpen} />
    </>
  );
}
