import dynamic from 'next/dynamic';
import { Skeleton } from '@/components/ui/skeleton';

const MediaLibraryPage = dynamic(
  () => import('@/components/cms/media-library-page').then((m) => m.MediaLibraryPage),
  { loading: () => <Skeleton className="h-[50vh] w-full" /> },
);

export default function CmsMediaPage() {
  return <MediaLibraryPage />;
}
