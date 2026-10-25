<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateEntityDraftRequest extends FormRequest
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
            'entity_type' => ['required', 'string', 'max:64'],
            'domain' => ['required', 'string', Rule::in(config('wikiworld.editor_domains', []))],
            'locale' => ['required', 'string', Rule::in(config('wikiworld.supported_locales', ['ar', 'en']))],
            'title' => ['required', 'string', 'max:500'],
            'slug' => ['sometimes', 'nullable', 'string', 'max:512'],
            'summary' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
