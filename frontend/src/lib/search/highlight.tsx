import type { ReactNode } from 'react';

const MARK_SPLIT = /(<mark>.*?<\/mark>)/gi;
const MARK_CAPTURE = /^<mark>(.*?)<\/mark>$/i;

/**
 * Renders backend highlight snippets safely by splitting on <mark> tags only.
 * Unmatched HTML-like content is shown as plain text (not parsed as HTML).
 */
export function renderHighlight(html: string | null | undefined): ReactNode {
  if (!html) return null;

  const parts = html.split(MARK_SPLIT);

  return parts.map((part, index) => {
    const markMatch = part.match(MARK_CAPTURE);
    if (markMatch) {
      return (
        <mark
          key={index}
          className="rounded-sm bg-[color-mix(in_srgb,var(--color-scholar)_22%,transparent)] text-[var(--color-ink)]"
        >
          {markMatch[1]}
        </mark>
      );
    }
    return <span key={index}>{part}</span>;
  });
}

/** Pure helper for tests — returns consecutive text / mark segments. */
export function parseHighlightSegments(
  html: string,
): Array<{ type: 'text' | 'mark'; value: string }> {
  if (!html) return [];

  const segments: Array<{ type: 'text' | 'mark'; value: string }> = [];

  for (const part of html.split(MARK_SPLIT)) {
    if (!part) continue;
    const markMatch = part.match(MARK_CAPTURE);
    if (markMatch) {
      segments.push({ type: 'mark', value: markMatch[1] });
    } else {
      segments.push({ type: 'text', value: part });
    }
  }

  return segments;
}
