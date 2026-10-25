<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class CreateEntityRelationshipRequest extends FormRequest
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
            'target_entity_uuid' => ['required', 'string', 'uuid'],
            'relationship_type' => ['required', 'string', 'max:64'],
            'metadata' => ['sometimes', 'array'],
        ];
    }
}
