import { getTranslations } from 'next-intl/server';
import { SearchBar } from '@/components/home/search-bar';

export async function Hero() {
  const t = await getTranslations('home');
  const app = await getTranslations('app');

  return (
    <section className="relative overflow-hidden rounded-3xl border border-[var(--color-border-strong)] bg-gradient-to-br from-[var(--primitive-teal-700)] to-[#042018] px-6 py-14 shadow-[var(--shadow-md)] sm:px-10 sm:py-20 text-white">
      {/* Decorative Islamic Background Pattern */}
      <div className="absolute inset-0 bg-islamic-pattern opacity-10 pointer-events-none" />
      
      {/* Glowing light effect representing spiritual wisdom */}
      <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(202,138,4,0.15),transparent_70%)]" />
      
      {/* Upper decorative border line in gold */}
      <div className="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-[var(--color-wisdom)] via-[var(--primitive-sepia-100)] to-[var(--color-wisdom)]" />

      <div className="relative mx-auto flex max-w-3xl flex-col items-center gap-6 text-center">
        <div className="inline-flex items-center gap-2 rounded-full bg-[rgba(202,138,4,0.1)] border border-[rgba(202,138,4,0.3)] px-4 py-1 text-xs font-semibold tracking-wider text-[var(--primitive-sepia-100)] uppercase">
          ✦ {app('name')} ✦
        </div>
        
        <h1 className="font-calligraphy text-4xl font-bold tracking-wide md:text-6xl text-[var(--primitive-sepia-100)] leading-relaxed drop-shadow-md">
          {t('heroTitle')}
        </h1>
        
        <p className="max-w-2xl text-base text-gray-200 md:text-lg leading-relaxed font-light">
          {t('heroSubtitle')}
        </p>

        <div className="mt-4 w-full max-w-xl">
          <SearchBar className="w-full text-black" />
        </div>
      </div>

      {/* Small calligraphic background flourish in the corner */}
      <div className="absolute bottom-4 right-6 text-xs text-gray-500 font-mono select-none pointer-events-none hidden md:block">
        بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ
      </div>
    </section>
  );
}
