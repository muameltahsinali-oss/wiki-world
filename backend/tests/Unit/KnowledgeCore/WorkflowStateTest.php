<?php

declare(strict_types=1);

namespace Tests\Unit\KnowledgeCore;

use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class WorkflowStateTest extends TestCase
{
    public function test_all_database_states_are_represented(): void
    {
        $this->assertSame(
            ['draft', 'submitted', 'in_review', 'approved', 'published', 'unpublished', 'archived'],
            WorkflowState::values(),
        );
    }

    #[DataProvider('stateProvider')]
    public function test_state_values_match_database_constraint(string $value): void
    {
        $this->assertSame($value, WorkflowState::from($value)->value);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function stateProvider(): array
    {
        return [
            'draft' => ['draft'],
            'submitted' => ['submitted'],
            'in_review' => ['in_review'],
            'approved' => ['approved'],
            'published' => ['published'],
            'unpublished' => ['unpublished'],
            'archived' => ['archived'],
        ];
    }
}
