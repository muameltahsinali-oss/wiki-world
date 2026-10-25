'use client';

import { X } from 'lucide-react';
import * as React from 'react';
import { createPortal } from 'react-dom';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';

export interface DialogProps {
  open: boolean;
  onOpenChange: (open: boolean) => void;
  title: string;
  description?: string;
  children?: React.ReactNode;
  className?: string;
}

export function Dialog({ open, onOpenChange, title, description, children, className }: DialogProps) {
  const dialogRef = React.useRef<HTMLDialogElement>(null);
  const [mounted, setMounted] = React.useState(false);

  React.useEffect(() => {
    setMounted(true);
  }, []);

  React.useEffect(() => {
    const dialog = dialogRef.current;
    if (!dialog) return;

    if (open && !dialog.open) {
      dialog.showModal();
    } else if (!open && dialog.open) {
      dialog.close();
    }
  }, [open]);

  React.useEffect(() => {
    const dialog = dialogRef.current;
    if (!dialog) return;

    const handleClose = () => onOpenChange(false);
    dialog.addEventListener('close', handleClose);
    return () => dialog.removeEventListener('close', handleClose);
  }, [onOpenChange]);

  if (!mounted) return null;

  return createPortal(
    <dialog
      ref={dialogRef}
      className={cn(
        'fixed inset-0 z-[var(--z-modal,400)] m-auto w-[min(100%-2rem,32rem)] max-h-[85vh] overflow-auto rounded-lg border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] p-0 shadow-[var(--shadow-lg)] backdrop:bg-[var(--color-surface-overlay)] open:animate-in',
        className,
      )}
      onClick={(event) => {
        if (event.target === dialogRef.current) {
          onOpenChange(false);
        }
      }}
    >
      <div className="flex items-start justify-between gap-4 border-b border-[var(--color-border-default)] p-4">
        <div>
          <h2 className="text-lg font-semibold text-[var(--color-ink)]">{title}</h2>
          {description ? (
            <p className="mt-1 text-sm text-[var(--color-ink-secondary)]">{description}</p>
          ) : null}
        </div>
        <Button
          type="button"
          variant="ghost"
          size="sm"
          className="h-8 w-8 shrink-0 p-0"
          onClick={() => onOpenChange(false)}
          aria-label="Close"
        >
          <X className="h-4 w-4" />
        </Button>
      </div>
      {children ? <div className="p-4">{children}</div> : null}
    </dialog>,
    document.body,
  );
}
