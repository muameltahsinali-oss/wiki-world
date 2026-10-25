<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Domain\Enums;

enum WorkflowState: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case InReview = 'in_review';
    case Approved = 'approved';
    case Published = 'published';
    case Unpublished = 'unpublished';
    case Archived = 'archived';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn (self $state): string => $state->value,
            self::cases(),
        );
    }
}
