<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateEntityReferenceRequest extends FormRequest
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
            'source_entity_uuid' => ['required', 'string', 'uuid'],
            'page_reference' => ['sometimes', 'nullable', 'string', 'max:255'],
            'quote_excerpt' => ['sometimes', 'nullable', 'string', 'max:2000'],
        ];
    }
}
