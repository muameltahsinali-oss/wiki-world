import { getTranslations, getLocale } from 'next-intl/server';
import { Link } from '@/i18n/navigation';
import { BookOpen } from 'lucide-react';

export async function VerseOfDay() {
  const t = await getTranslations('home');
  const locale = await getLocale();

  // We showcase Ayat al-Tathir (33:33), central to Shiite/Ahlulbayt belief, with high-end calligraphic design.
  return (
    <section className="relative overflow-hidden rounded-3xl border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] px-6 py-8 shadow-[var(--shadow-sm)] sm:px-8">
      {/* Small top accent gold line */}
      <div className="absolute top-0 left-0 right-0 h-1 bg-[var(--color-wisdom)]" />
      
      {/* Decorative background star badge */}
      <div className="absolute -right-16 -top-16 w-32 h-32 bg-[var(--color-scholar-subtle)] rounded-full opacity-30 blur-2xl pointer-events-none" />

      <div className="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
        <div className="flex flex-col gap-4 flex-1">
          <div className="flex items-center gap-2">
            <span className="flex h-8 w-8 items-center justify-center rounded-lg bg-[var(--color-scholar-subtle)] text-[var(--color-scholar)]">
              <BookOpen className="h-4 w-4" />
            </span>
            <h2 className="text-lg font-semibold text-[var(--color-ink)]">
              {locale === 'ar' ? 'من التنزيل الحكيم: آية التطهير (الأحزاب: 33)' : 'From the Holy Quran: Verse of Purification (33:33)'}
            </h2>
          </div>
          
          <div className="flex flex-col gap-3">
            {/* Arabic Verse with Amiri calligraphy font */}
            <p className="font-calligraphy text-2xl md:text-3xl text-[var(--color-scholar)] font-bold leading-relaxed text-right md:text-start" dir="rtl">
              «إِنَّمَا يُرِيدُ اللَّهُ لِيُذْهِبَ عَنكُمُ الرِّجْسَ أَهْلَ الْبَيْتِ وَيُطَهِّرَكُمْ تَطْهِيرًا»
            </p>
            
            {/* English translation */}
            <p className="text-sm italic text-[var(--color-ink-secondary)] leading-relaxed">
              "Allah only wishes to remove all abomination from you, ye members of the Family, and to make you pure and spotless."
            </p>
          </div>
        </div>

        <div className="flex shrink-0">
          <Link
            href="/quran/surahs/33"
            className="inline-flex h-10 items-center justify-center rounded-md bg-[var(--color-scholar-subtle)] px-4 text-xs font-medium text-[var(--color-scholar)] border border-[var(--color-scholar-muted)] hover:bg-[var(--color-scholar)] hover:text-white transition-colors"
          >
            تفسير الآية وقراءة السورة
          </Link>
        </div>
      </div>
    </section>
  );
}
