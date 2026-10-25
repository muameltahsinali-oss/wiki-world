'use client';

import { useQuery } from '@tanstack/react-query';
import { useLocale, useTranslations } from 'next-intl';
import { MapPin, Compass, Landmark } from 'lucide-react';
import { getPublicEntities } from '@/lib/api/public';
import { Skeleton } from '@/components/ui/skeleton';
import { Link } from '@/i18n/navigation';
import { entityPublicHref } from '@/lib/entity/types';

export function HolyPlaces() {
  const locale = useLocale();
  const t = useTranslations('home');

  const placesQuery = useQuery({
    queryKey: ['public-places', locale],
    queryFn: () => getPublicEntities(locale, 'places', { per_page: 5 }),
  });

  const places = placesQuery.data?.data ?? [];

  return (
    <section className="flex flex-col gap-8 py-4">
      <div className="flex flex-col gap-2">
        <div className="flex items-center gap-2">
          <span className="h-6 w-1 bg-[var(--color-wisdom)] rounded-full" />
          <h2 className="text-2xl font-bold tracking-tight text-[var(--color-ink)] font-calligraphy">
            {locale === 'ar' ? 'الأماكن الجغرافية والمعالم التاريخية' : 'Geographical Places & Historical Landmarks'}
          </h2>
        </div>
        <p className="text-[var(--color-ink-secondary)] text-sm max-w-xl font-light">
          {locale === 'ar' 
            ? 'استكشف أهم المعالم الجغرافية والدينية المرتبطة بتاريخ العراق العريق ومراقد أهل البيت (ع).' 
            : 'Explore the key geographical and religious landmarks linked to Iraq\'s rich history and the shrines of the Ahlulbayt (a).'}
        </p>
      </div>

      {placesQuery.isLoading ? (
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {[...Array(3)].map((_, idx) => (
            <div key={idx} className="h-64 rounded-3xl border border-[var(--color-border-default)] p-6 bg-white animate-pulse">
              <Skeleton className="h-6 w-1/3 mb-4" />
              <Skeleton className="h-4 w-full mb-2" />
              <Skeleton className="h-4 w-3/4 mb-4" />
              <Skeleton className="h-10 w-24 rounded-md" />
            </div>
          ))}
        </div>
      ) : places.length > 0 ? (
        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
          {places.map((place) => (
            <div key={place.uuid} className="mihrab-arch flex flex-col justify-between p-6 bg-white border border-[var(--color-border-default)] relative">
              {/* Islamic tile design accent in the corner */}
              <div className="absolute top-3 right-4 text-xs font-mono text-[var(--color-wisdom)] opacity-30 select-none">
                🕌
              </div>
              
              <div>
                <span className="inline-flex items-center gap-1 text-xs font-medium text-[var(--color-wisdom)] uppercase tracking-widest mb-3">
                  <Landmark className="h-3 w-3" />
                  {place.type === 'place' && locale === 'ar' ? 'معلم / مرقد' : 'Shrine / Landmark'}
                </span>
                
                <h3 className="text-xl font-bold font-calligraphy text-[var(--color-scholar)] mb-3 leading-relaxed">
                  {place.title}
                </h3>
                
                <p className="text-sm text-[var(--color-ink-secondary)] leading-relaxed mb-6 font-light">
                  {place.summary}
                </p>
              </div>

              <div className="flex flex-col gap-4 border-t border-[var(--color-border-default)] pt-4">
                {/* Coordinates */}
                <div className="flex items-center gap-2 text-xs text-[var(--color-ink-tertiary)] font-mono">
                  <Compass className="h-3.5 w-3.5 text-[var(--color-wisdom)]" />
                  <span>العراق (IQ)</span>
                </div>
                
                <Link
                  href={entityPublicHref('place', place.uuid)}
                  className="inline-flex h-9 items-center justify-center rounded-lg bg-[var(--color-scholar)] text-xs font-medium text-white hover:bg-[var(--color-scholar-hover)] transition-colors w-full"
                >
                  {locale === 'ar' ? 'عرض التفاصيل والزيارة' : 'View details'}
                </Link>
              </div>
            </div>
          ))}
        </div>
      ) : (
        <div className="rounded-2xl border border-dashed border-[var(--color-border-default)] p-8 text-center text-sm text-[var(--color-ink-tertiary)] bg-[var(--color-surface-sunken)]">
          {locale === 'ar' ? 'لا توجد معالم مضافة حالياً.' : 'No landmarks available.'}
        </div>
      )}
    </section>
  );
}
