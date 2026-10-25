import { describe, expect, it } from 'vitest';
import { resolvePersonLabelKey } from '@/lib/entity/person-label';

describe('resolvePersonLabelKey', () => {
  it('labels numbered imams as masumImam', () => {
    expect(resolvePersonLabelKey({ is_masum: true, imam_order: 3 }, 'الإمام الحسين')).toBe('masumImam');
  });

  it('labels Imam Ali without imam_order via title', () => {
    expect(resolvePersonLabelKey({ is_masum: true, imam_order: null }, 'الإمام علي')).toBe('masumImam');
  });

  it('labels the Prophet via generation', () => {
    expect(resolvePersonLabelKey({ is_masum: true, generation: 0 }, 'النبي محمد')).toBe('prophet');
  });

  it('labels Fatimah as masumLady', () => {
    expect(
      resolvePersonLabelKey({ is_masum: true, generation: 1 }, 'السيدة فاطمة الزهراء'),
    ).toBe('masumLady');
  });

  it('uses ahlulbaytFigure in ahlulbayt module without masum hints', () => {
    expect(resolvePersonLabelKey(null, 'عبد الله بن جعفر', 'ahlulbayt')).toBe('ahlulbaytFigure');
  });

  it('falls back to figure for generic persons', () => {
    expect(resolvePersonLabelKey(null, 'عالم تاريخي')).toBe('figure');
  });
});
