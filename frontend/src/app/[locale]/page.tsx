import { Hero } from '@/components/home/hero';
import { ModuleCards } from '@/components/home/module-cards';
import { VerseOfDay } from '@/components/home/verse-of-day';
import { HolyPlaces } from '@/components/home/holy-places';

export default async function HomePage() {
  return (
    <main className="mx-auto flex w-full max-w-7xl flex-col gap-12 px-4 py-10 sm:px-6 sm:py-14">
      <Hero />
      <VerseOfDay />
      <ModuleCards />
      <HolyPlaces />
    </main>
  );
}
