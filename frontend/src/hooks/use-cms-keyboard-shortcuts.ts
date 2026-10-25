'use client';

import * as React from 'react';

interface CmsKeyboardShortcutsOptions {
  onSave: () => void;
  onSubmit: () => void;
  onCommandPalette: () => void;
}

export function useCmsKeyboardShortcuts({
  onSave,
  onSubmit,
  onCommandPalette,
}: CmsKeyboardShortcutsOptions) {
  const onSaveRef = React.useRef(onSave);
  const onSubmitRef = React.useRef(onSubmit);
  const onCommandPaletteRef = React.useRef(onCommandPalette);

  React.useEffect(() => {
    onSaveRef.current = onSave;
    onSubmitRef.current = onSubmit;
    onCommandPaletteRef.current = onCommandPalette;
  });

  React.useEffect(() => {
    function handleKeyDown(event: KeyboardEvent) {
      const mod = event.metaKey || event.ctrlKey;
      if (!mod) return;

      const key = event.key.toLowerCase();

      if (key === 's') {
        event.preventDefault();
        onSaveRef.current();
      } else if (key === 'enter') {
        event.preventDefault();
        onSubmitRef.current();
      } else if (key === 'k') {
        event.preventDefault();
        onCommandPaletteRef.current();
      }
    }

    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, []);
}
