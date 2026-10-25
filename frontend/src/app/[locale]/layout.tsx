import type { Metadata } from 'next';
import { getMessages, setRequestLocale } from 'next-intl/server';
import { notFound } from 'next/navigation';
import { Footer } from '@/components/layout/footer';
import { Header } from '@/components/layout/header';
import { LocaleHtmlAttributes } from '@/components/layout/locale-html-attributes';
import { SkipLink } from '@/components/layout/skip-link';
import { routing } from '@/i18n/routing';
import { AppProviders } from '@/providers/app-providers';

export function generateStaticParams() {
  return routing.locales.map((locale) => ({ locale }));
}

export const metadata: Metadata = {
  title: {
    default: 'Wiki World | ويكي العالمية',
    template: '%s | Wiki World',
  },
  description: "Ja'fari Islamic knowledge platform",
};

export default async function LocaleLayout({
  children,
  params,
}: {
  children: React.ReactNode;
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;

  if (!routing.locales.includes(locale as 'ar' | 'en')) {
    notFound();
  }

  setRequestLocale(locale);
  const messages = await getMessages();

  return (
    <AppProviders locale={locale} messages={messages}>
      <LocaleHtmlAttributes />
      <SkipLink />
      <Header />
      <div id="main-content" className="flex-1">
        {children}
      </div>
      <Footer />
    </AppProviders>
  );
}
