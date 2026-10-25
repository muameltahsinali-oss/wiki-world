'use client';

import { Search } from 'lucide-react';
import { useLocale, useTranslations } from 'next-intl';
import { useEffect, useId, useRef, useState } from 'react';
import { useQuery } from '@tanstack/react-query';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge, entityBadgeVariant } from '@/components/ui/badge';
import { Link, useRouter } from '@/i18n/navigation';
import { suggestions as fetchSuggestions } from '@/lib/api/search';
import { entityPublicHref } from '@/lib/entity/types';
import { useEntityTypeLabel } from '@/lib/entity/person-label';
import { cn } from '@/lib/utils';

const DEBOUNCE_MS = 300;

export function SearchBar({
  className,
  initialQuery = '',
}: {
  className?: string;
  initialQuery?: string;
}) {
  const t = useTranslations('home');
  const tSearch = useTranslations('search');
  const entityTypeLabel = useEntityTypeLabel();
  const locale = useLocale();
  const router = useRouter();
  const listboxId = useId();
  const containerRef = useRef<HTMLDivElement>(null);

  const [query, setQuery] = useState(initialQuery);
  const [debouncedQuery, setDebouncedQuery] = useState(initialQuery);
  const [open, setOpen] = useState(false);
  const [activeIndex, setActiveIndex] = useState(-1);

  useEffect(() => {
    const timer = window.setTimeout(() => setDebouncedQuery(query.trim()), DEBOUNCE_MS);
    return () => window.clearTimeout(timer);
  }, [query]);

  const suggestionsQuery = useQuery({
    queryKey: ['search-suggestions', locale, debouncedQuery],
    queryFn: () => fetchSuggestions(debouncedQuery, locale),
    enabled: debouncedQuery.length >= 1,
  });

  const items = suggestionsQuery.data?.data ?? [];

  useEffect(() => {
    setActiveIndex(-1);
    setOpen(debouncedQuery.length >= 1 && items.length > 0);
  }, [debouncedQuery, items.length]);

  useEffect(() => {
    const handlePointer = (event: MouseEvent) => {
      if (!containerRef.current?.contains(event.target as Node)) {
        setOpen(false);
      }
    };
    document.addEventListener('mousedown', handlePointer);
    return () => document.removeEventListener('mousedown', handlePointer);
  }, []);

  const navigateToSearch = (value: string) => {
    const q = value.trim();
    if (!q) return;
    setOpen(false);
    router.push(`/search?q=${encodeURIComponent(q)}`);
  };

  const handleSubmit = (event: React.FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (activeIndex >= 0 && items[activeIndex]) {
      const item = items[activeIndex];
      setOpen(false);
      router.push(entityPublicHref(item.type, item.uuid, item.slug));
      return;
    }
    navigateToSearch(query);
  };

  const handleKeyDown = (event: React.KeyboardEvent<HTMLInputElement>) => {
    if (!open || items.length === 0) return;

    if (event.key === 'ArrowDown') {
      event.preventDefault();
      setActiveIndex((index) => (index + 1) % items.length);
    } else if (event.key === 'ArrowUp') {
      event.preventDefault();
      setActiveIndex((index) => (index <= 0 ? items.length - 1 : index - 1));
    } else if (event.key === 'Escape') {
      setOpen(false);
    }
  };

  return (
    <form onSubmit={handleSubmit} className={cn('flex gap-2', className)}>
      <div ref={containerRef} className="relative flex-1">
        <Search
          className="pointer-events-none absolute start-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[var(--color-ink-tertiary)]"
          aria-hidden
        />
        <Input
          value={query}
          onChange={(event) => setQuery(event.target.value)}
          onFocus={() => {
            if (items.length > 0) setOpen(true);
          }}
          onKeyDown={handleKeyDown}
          placeholder={t('searchPlaceholder')}
          className="ps-9"
          aria-label={t('searchPlaceholder')}
          aria-autocomplete="list"
          aria-controls={listboxId}
          aria-expanded={open}
          role="combobox"
          autoComplete="off"
        />

        {open && items.length > 0 ? (
          <ul
            id={listboxId}
            role="listbox"
            aria-label={tSearch('suggestions')}
            className="absolute inset-x-0 top-[calc(100%+0.35rem)] z-[var(--z-dropdown,100)] max-h-72 overflow-auto rounded-lg border border-[var(--color-border-default)] bg-[var(--color-surface-raised)] py-1 shadow-[var(--shadow-md)]"
          >
            {items.map((item, index) => (
              <li key={`${item.uuid}-${index}`} role="option" aria-selected={index === activeIndex}>
                <Link
                  href={entityPublicHref(item.type, item.uuid, item.slug)}
                  className={cn(
                    'flex items-center justify-between gap-3 px-3 py-2 text-sm text-[var(--color-ink)] hover:bg-[var(--color-surface-sunken)]',
                    index === activeIndex && 'bg-[var(--color-surface-sunken)]',
                  )}
                  onClick={() => setOpen(false)}
                >
                  <span className="truncate">{item.text}</span>
                  <Badge variant={entityBadgeVariant(item.type)}>
                    {entityTypeLabel(item.type, { title: item.text })}
                  </Badge>
                </Link>
              </li>
            ))}
          </ul>
        ) : null}
      </div>
      <Button type="submit">{t('searchButton')}</Button>
    </form>
  );
}
