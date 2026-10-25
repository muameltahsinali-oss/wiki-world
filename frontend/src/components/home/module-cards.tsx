import { BookOpen, Library, ScrollText, Users } from 'lucide-react';
import { getTranslations } from 'next-intl/server';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Link } from '@/i18n/navigation';

export async function ModuleCards() {
  const t = await getTranslations('home');

  const modules = [
    {
      href: '/quran',
      title: t('moduleQuran'),
      description: t('moduleQuranDesc'),
      icon: BookOpen,
    },
    {
      href: '/ahlulbayt',
      title: t('moduleAhlulbayt'),
      description: t('moduleAhlulbaytDesc'),
      icon: Users,
    },
    {
      href: '/articles',
      title: t('moduleArticles'),
      description: t('moduleArticlesDesc'),
      icon: ScrollText,
    },
    {
      href: '/sources',
      title: t('moduleSources'),
      description: t('moduleSourcesDesc'),
      icon: Library,
    },
  ] as const;

  return (
    <section className="flex flex-col gap-6">
      <div className="flex flex-col gap-2">
        <h2 className="text-2xl font-semibold text-[var(--color-ink)]">{t('modulesTitle')}</h2>
        <p className="text-[var(--color-ink-secondary)]">{t('modulesSubtitle')}</p>
      </div>
      <div className="grid gap-4 sm:grid-cols-2">
        {modules.map((module) => {
          const Icon = module.icon;
          return (
            <Link key={module.href} href={module.href} className="group outline-none">
              <Card variant="interactive" className="h-full">
                <CardHeader>
                  <div className="mb-2 flex h-10 w-10 items-center justify-center rounded-lg bg-[var(--color-scholar-subtle)] text-[var(--color-scholar)] transition-colors group-hover:bg-[var(--color-scholar)] group-hover:text-white">
                    <Icon className="h-5 w-5" aria-hidden />
                  </div>
                  <CardTitle>{module.title}</CardTitle>
                  <CardDescription>{module.description}</CardDescription>
                </CardHeader>
              </Card>
            </Link>
          );
        })}
      </div>
    </section>
  );
}
