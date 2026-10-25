<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveEntityDraftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', Rule::in(config('wikiworld.supported_locales', ['ar', 'en']))],
            'title' => ['sometimes', 'nullable', 'string', 'max:500'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:512'],
            'summary' => ['sometimes', 'nullable', 'string'],
            'profile' => ['sometimes', 'nullable', 'array'],
            'blocks' => ['sometimes', 'nullable', 'array'],
            'blocks.*.type' => ['sometimes', 'string', 'max:32'],
            'blocks.*.block_type' => ['sometimes', 'string', 'max:32'],
            'blocks.*.content' => ['sometimes'],
            'blocks.*.heading_level' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:6'],
            'blocks.*.sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
