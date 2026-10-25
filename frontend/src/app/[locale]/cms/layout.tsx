import {
  FileText,
  Image,
  LayoutDashboard,
  Settings,
  ShieldCheck,
} from 'lucide-react';
import { getTranslations } from 'next-intl/server';
import { EditorialAuthGuard } from '@/components/cms/editorial-auth-guard';
import { Link } from '@/i18n/navigation';
import { cn } from '@/lib/utils';

const navItems = [
  { href: '/cms', icon: LayoutDashboard, key: 'dashboard' },
  { href: '/cms/entities', icon: FileText, key: 'entities' },
  { href: '/cms/reviews', icon: ShieldCheck, key: 'reviews' },
  { href: '/cms/media', icon: Image, key: 'media' },
  { href: '/cms/settings', icon: Settings, key: 'settings' },
] as const;

export default async function CmsLayout({ children }: { children: React.ReactNode }) {
  const t = await getTranslations('cms');

  return (
    <EditorialAuthGuard>
      <div className="flex min-h-[calc(100vh-var(--header-height))] bg-[var(--color-surface-base)]">
        <aside className="flex w-16 shrink-0 flex-col items-center gap-2 border-e border-[var(--color-border-default)] bg-[var(--color-surface-raised)] py-4">
          {navItems.map((item) => {
            const Icon = item.icon;
            return (
              <Link
                key={item.href}
                href={item.href}
                className={cn(
                  'flex h-10 w-10 items-center justify-center rounded-lg text-[var(--color-ink-secondary)] transition-colors hover:bg-[var(--color-surface-sunken)] hover:text-[var(--color-scholar)]',
                )}
                title={t(item.key)}
                aria-label={t(item.key)}
              >
                <Icon className="h-5 w-5" />
              </Link>
            );
          })}
        </aside>

        <div className="flex min-w-0 flex-1">
          <section className="min-w-0 flex-1 p-6">{children}</section>
        </div>
      </div>
    </EditorialAuthGuard>
  );
}
