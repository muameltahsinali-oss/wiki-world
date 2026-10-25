import { useTranslations } from 'next-intl';
import { Badge } from '@/components/ui/badge';
import type { WorkflowState } from '@/lib/cms/types';

const variantMap: Record<WorkflowState, 'default' | 'info' | 'warning' | 'success' | 'error'> = {
  draft: 'default',
  submitted: 'info',
  in_review: 'warning',
  approved: 'success',
  published: 'success',
  unpublished: 'warning',
  archived: 'error',
};

export function WorkflowBadge({ state }: { state: WorkflowState }) {
  const t = useTranslations('cms.workflow');

  return <Badge variant={variantMap[state]}>{t(state)}</Badge>;
}
