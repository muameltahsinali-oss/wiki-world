'use client';

import { X } from 'lucide-react';
import * as React from 'react';
import { createPortal } from 'react-dom';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export interface DrawerProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  title: string;
  children?: React.ReactNode;
  side?: 'start' | 'end';
  className?: string;
}

export function Drawer({
  open,
  onOpenChange,
  title,
  children,
  side = 'end',
  className,
}: DrawerProps) {
  const panelRef = React.useRef<HTMLDivElement>(null);
  const [mounted, setMounted] = React.useState(false);

  React.useEffect(() => {
    setMounted(true);
  }, []);

  React.useEffect(() => {
    if (!open) return;

    const handleKeyDown = (event: KeyboardEvent) => {
      if (event.key === 'Escape') {
        onOpenChange(false);
      }
    };

    document.addEventListener('keydown', handleKeyDown);
    document.body.style.overflow = 'hidden';
    panelRef.current?.focus();

    return () => {
      document.removeEventListener('keydown', handleKeyDown);
      document.body.style.overflow = '';
    };
  }, [open, onOpenChange]);

  if (!mounted || !open) return null;

  return createPortal(
    <div className="fixed inset-0 z-[var(--z-drawer,300)]">
      <button
        type="button"
        className="absolute inset-0 bg-[var(--color-surface-overlay)]"
        aria-label="Close drawer"
        onClick={() => onOpenChange(false)}
      />
      <div
        ref={panelRef}
        tabIndex={-1}
        role="dialog"
        aria-modal="true"
        aria-label={title}
        className={cn(
          'absolute top-0 flex h-full w-[min(100%,24rem)] flex-col border-[var(--color-border-default)] bg-[var(--color-surface-raised)] shadow-[var(--shadow-lg)] outline-none',
          side === 'end' ? 'end-0 border-s' : 'start-0 border-e',
          className,
        )}
      >
        <div className="flex items-center justify-between border-b border-[var(--color-border-default)] p-4">
          <h2 className="text-lg font-semibold text-[var(--color-ink)]">{title}</h2>
          <Button
            type="button"
            variant="ghost"
            size="sm"
            className="h-8 w-8 p-0"
            onClick={() => onOpenChange(false)}
            aria-label="Close"
          >
            <X className="h-4 w-4" />
          </Button>
        </div>
        <div className="flex-1 overflow-auto p-4">{children}</div>
      </div>
    </div>,
    document.body,
  );
}
