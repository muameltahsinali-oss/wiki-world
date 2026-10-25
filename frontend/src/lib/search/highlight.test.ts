import { describe, expect, it } from 'vitest';

import { parseHighlightSegments } from './highlight';

describe('parseHighlightSegments', () => {
  it('returns empty array for empty input', () => {
    expect(parseHighlightSegments('')).toEqual([]);
  });

  it('keeps plain text as a single segment', () => {
    expect(parseHighlightSegments('Hello world')).toEqual([
      { type: 'text', value: 'Hello world' },
    ]);
  });

  it('extracts mark segments from highlight HTML', () => {
    expect(parseHighlightSegments('The <mark>Ghadir</mark> event')).toEqual([
      { type: 'text', value: 'The ' },
      { type: 'mark', value: 'Ghadir' },
      { type: 'text', value: ' event' },
    ]);
  });

  it('handles multiple marks', () => {
    expect(parseHighlightSegments('<mark>Ali</mark> and <mark>Fatima</mark>')).toEqual([
      { type: 'mark', value: 'Ali' },
      { type: 'text', value: ' and ' },
      { type: 'mark', value: 'Fatima' },
    ]);
  });

  it('does not interpret non-mark tags as HTML', () => {
    expect(parseHighlightSegments('See <script>alert(1)</script>')).toEqual([
      { type: 'text', value: 'See <script>alert(1)</script>' },
    ]);
  });
});
