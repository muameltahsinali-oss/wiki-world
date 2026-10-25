'use client';

import { useEffect, useState } from 'react';
import { cn } from '@/lib/utils';

export function ReadingProgressBar({ className }: { className?: string }) {
  const [progress, setProgress] = useState(0);

  useEffect(() => {
    const updateProgress = () => {
      const content = document.querySelector('[data-entity-content]');
      if (!content) return;

      const rect = content.getBoundingClientRect();
      const contentTop = rect.top + window.scrollY;
      const contentHeight = content.scrollHeight;
      const viewport = window.innerHeight;
      const scrolled = window.scrollY - contentTop + viewport * 0.2;
      const total = contentHeight;
      const ratio = Math.min(Math.max(scrolled / total, 0), 1);
      setProgress(ratio * 100);
    };

    updateProgress();
    window.addEventListener('scroll', updateProgress, { passive: true });
    window.addEventListener('resize', updateProgress);
    return () => {
      window.removeEventListener('scroll', updateProgress);
      window.removeEventListener('resize', updateProgress);
    };
  }, []);

  return (
    <div
      className={cn('fixed inset-x-0 top-[var(--header-height)] z-40 h-0.5 bg-transparent', className)}
      role="progressbar"
      aria-valuenow={Math.round(progress)}
      aria-valuemin={0}
      aria-valuemax={100}
      aria-label="Reading progress"
    >
      <div
        className="h-full bg-[var(--color-scholar)] transition-[width] duration-150"
        style={{ width: `${progress}%` }}
      />
    </div>
  );
}
