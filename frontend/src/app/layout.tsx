import type { Metadata } from 'next';
import { amiri, ibmPlexSansArabic, inter } from '@/lib/fonts';
import { cn } from '@/lib/utils';
import './globals.css';

export const metadata: Metadata = {
  title: 'Wiki World',
};

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="ar" suppressHydrationWarning>
      <body
        className={cn(
          'flex min-h-screen flex-col antialiased',
          inter.variable,
          ibmPlexSansArabic.variable,
          amiri.variable,
        )}
      >
        {children}
      </body>
    </html>
  );
}
