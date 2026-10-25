<?php

declare(strict_types=1);

namespace App\Modules\Publishing\Application\Services;

use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use App\Modules\Publishing\Domain\Exceptions\InvalidWorkflowTransitionException;

final class PublishingWorkflowService
{
    /**
     * @var array<string, array{from: WorkflowState, to: WorkflowState}>
     */
    private const TRANSITIONS = [
        'submit' => ['from' => WorkflowState::Draft, 'to' => WorkflowState::Submitted],
        'auto_review' => ['from' => WorkflowState::Submitted, 'to' => WorkflowState::InReview],
        'withdraw' => ['from' => WorkflowState::Submitted, 'to' => WorkflowState::Draft],
        'reject' => ['from' => WorkflowState::InReview, 'to' => WorkflowState::Draft],
        'approve' => ['from' => WorkflowState::InReview, 'to' => WorkflowState::Approved],
        'publish' => ['from' => WorkflowState::Approved, 'to' => WorkflowState::Published],
        'unpublish' => ['from' => WorkflowState::Published, 'to' => WorkflowState::Unpublished],
        're_edit' => ['from' => WorkflowState::Unpublished, 'to' => WorkflowState::Draft],
        'archive' => ['from' => WorkflowState::Published, 'to' => WorkflowState::Archived],
    ];

    public function resolve(WorkflowState $current, string $action): WorkflowState
    {
        $transition = self::TRANSITIONS[$action] ?? null;

        if ($transition === null) {
            throw new InvalidWorkflowTransitionException("Unknown workflow action: {$action}.");
        }

        if ($transition['from'] !== $current) {
            throw new InvalidWorkflowTransitionException(
                "Cannot perform '{$action}' from state '{$current->value}'.",
            );
        }

        return $transition['to'];
    }

    /**
     * @return list<string>
     */
    public function allowedActions(WorkflowState $current): array
    {
        $actions = [];

        foreach (self::TRANSITIONS as $action => $transition) {
            if ($transition['from'] === $current) {
                $actions[] = $action;
            }
        }

        return $actions;
    }
}
