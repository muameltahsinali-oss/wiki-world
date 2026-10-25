<?php

declare(strict_types=1);

namespace App\Modules\Publishing\Http\Controllers;

use App\Modules\Publishing\Application\Actions\ReviewSubmissionAction;
use App\Modules\Publishing\Http\Requests\ReviewSubmissionRequest;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\Users\Infrastructure\Models\User;
use Illuminate\Http\JsonResponse;

final class ReviewSubmissionController
{
    public function __construct(
        private readonly ReviewSubmissionAction $reviewSubmission,
    ) {}

    public function __invoke(ReviewSubmissionRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $result = $this->reviewSubmission->execute(
            user: $user,
            submissionId: $id,
            decision: $request->string('decision')->toString(),
            notes: $request->has('notes') ? $request->string('notes')->toString() : null,
        );

        return ApiResponse::success($result);
    }
}
