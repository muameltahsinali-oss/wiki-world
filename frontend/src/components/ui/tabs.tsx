'use client';

import * as React from 'react';
import { cn } from '@/lib/utils';

export interface TabItem {
  id: string;
  label: string;
  content: React.ReactNode;
}

export interface TabsProps {
  items: TabItem[];
  defaultTab?: string;
  className?: string;
}

export function Tabs({ items, defaultTab, className }: TabsProps) {
  const [activeTab, setActiveTab] = React.useState(defaultTab ?? items[0]?.id ?? '');

  const handleKeyDown = (event: React.KeyboardEvent<HTMLButtonElement>, index: number) => {
    let nextIndex = index;

    if (event.key === 'ArrowRight' || event.key === 'ArrowDown') {
      nextIndex = (index + 1) % items.length;
    } else if (event.key === 'ArrowLeft' || event.key === 'ArrowUp') {
      nextIndex = (index - 1 + items.length) % items.length;
    } else if (event.key === 'Home') {
      nextIndex = 0;
    } else if (event.key === 'End') {
      nextIndex = items.length - 1;
    } else {
      return;
    }

    event.preventDefault();
    setActiveTab(items[nextIndex].id);
  };

  const activeContent = items.find((item) => item.id === activeTab)?.content;

  return (
    <div className={cn('flex flex-col gap-4', className)}>
      <div role="tablist" className="flex flex-wrap gap-1 border-b border-[var(--color-border-default)]">
        {items.map((item, index) => {
          const selected = item.id === activeTab;
          return (
            <button
              key={item.id}
              type="button"
              role="tab"
              id={`tab-${item.id}`}
              aria-selected={selected}
              aria-controls={`panel-${item.id}`}
              tabIndex={selected ? 0 : -1}
              className={cn(
                'px-4 py-2 text-sm font-medium transition-colors',
                selected
                  ? 'border-b-2 border-[var(--color-scholar)] text-[var(--color-scholar)]'
                  : 'text-[var(--color-ink-secondary)] hover:text-[var(--color-ink)]',
              )}
              onClick={() => setActiveTab(item.id)}
              onKeyDown={(event) => handleKeyDown(event, index)}
            >
              {item.label}
            </button>
          );
        })}
      </div>
      <div
        role="tabpanel"
        id={`panel-${activeTab}`}
        aria-labelledby={`tab-${activeTab}`}
        className="text-sm text-[var(--color-ink-secondary)]"
      >
        {activeContent}
      </div>
    </div>
  );
}
