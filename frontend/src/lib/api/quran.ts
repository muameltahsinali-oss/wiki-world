import { apiRequest } from '@/lib/api/client';
import type { ApiDataResponse } from '@/lib/api/endpoints';
import type { EntityType } from '@/components/ui/badge';

export interface QuranSurahSummary {
  number: number;
  name_ar: string;
  name_transliterated: string;
  name_translated: string;
  revelation_type: 'meccan' | 'medinan';
  verse_count: number;
  juz_numbers?: number[];
}

export interface QuranSurahDetail extends QuranSurahSummary {
  bismillah?: string;
  verses_preview?: QuranVerseSummary[];
  related_topics?: Array<{ slug: string; title: string }>;
  statistics?: Record<string, number>;
}

export interface QuranVerseSummary {
  uuid?: string;
  surah: number;
  ayah: number;
  reference: string;
  text_uthmani: string;
  text_simple?: string;
}

export interface QuranVerseTranslation {
  locale: string;
  text: string;
  translator: string;
  source_uuid?: string;
}

export interface QuranVerseDetail extends QuranVerseSummary {
  juz?: number;
  hizb?: number;
  page?: number;
  sajdah?: boolean;
  translations?: QuranVerseTranslation[];
  related_entities?: Array<{ uuid: string; type: EntityType; title: string }>;
  cross_references?: Array<{ surah: number; ayah: number; relationship: string }>;
  tafsir?: Array<{ excerpt: string; source: string; scholar?: string }>;
}

export interface QuranLanding {
  featured_surahs: QuranSurahSummary[];
  featured_topics: Array<{ slug: string; title: string; verse_count?: number }>;
  statistics?: Record<string, number>;
}

export function parseVerseReference(reference: string): { surah: number; ayah: number } | null {
  const match = /^(\d{1,3}):(\d{1,3})$/.exec(reference.trim());
  if (!match) return null;
  return { surah: Number(match[1]), ayah: Number(match[2]) };
}

export function getQuranLanding(locale: string) {
  return apiRequest<ApiDataResponse<QuranLanding>>(`/${locale}/quran`, { locale });
}

export function getQuranSurahs(locale: string) {
  return apiRequest<ApiDataResponse<QuranSurahSummary[]>>(`/${locale}/quran/surahs`, { locale });
}

export function getQuranSurah(locale: string, number: number) {
  return apiRequest<ApiDataResponse<QuranSurahDetail>>(`/${locale}/quran/surahs/${number}`, { locale });
}

export function getQuranSurahVerses(locale: string, number: number) {
  return apiRequest<ApiDataResponse<QuranVerseSummary[]>>(
    `/${locale}/quran/surahs/${number}/verses`,
    { locale },
  );
}

export function getQuranVerse(locale: string, surah: number, ayah: number) {
  return apiRequest<ApiDataResponse<QuranVerseDetail>>(
    `/${locale}/quran/verses/${surah}:${ayah}`,
    { locale },
  );
}
