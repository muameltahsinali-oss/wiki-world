'use client';

import { Moon, Sun, Monitor } from 'lucide-react';
import { useTranslations } from 'next-intl';
import { Button } from '@/components/ui/button';
import { useTheme } from '@/providers/theme-provider';

export function ThemeToggle() {
  const t = useTranslations('theme');
  const { theme, setTheme } = useTheme();

  const cycleTheme = () => {
    const order = ['light', 'dark', 'system'] as const;
    const currentIndex = order.indexOf(theme);
    const next = order[(currentIndex + 1) % order.length];
    setTheme(next);
  };

  const Icon = theme === 'dark' ? Moon : theme === 'light' ? Sun : Monitor;
  const label =
    theme === 'dark' ? t('dark') : theme === 'light' ? t('light') : t('system');

  return (
    <Button
      type="button"
      variant="ghost"
      size="sm"
      onClick={cycleTheme}
      aria-label={label}
      title={label}
      className="h-9 w-9 p-0"
    >
      <Icon className="h-4 w-4" />
    </Button>
  );
}
