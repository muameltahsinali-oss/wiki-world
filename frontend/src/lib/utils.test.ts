import { describe, expect, it } from 'vitest';

import { cn, isRtlLocale } from './utils';

describe('utils', () => {
  it('merges class names with tailwind precedence', () => {
    expect(cn('px-2', 'px-4')).toBe('px-4');
  });

  it('detects RTL locales', () => {
    expect(isRtlLocale('ar')).toBe(true);
    expect(isRtlLocale('en')).toBe(false);
  });
});
