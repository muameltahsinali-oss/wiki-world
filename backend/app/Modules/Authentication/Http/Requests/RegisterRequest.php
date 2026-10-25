<?php

declare(strict_types=1);

namespace App\Modules\Authentication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Laravel\Sanctum\PersonalAccessToken;

final class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        $token = $this->bearerToken();

        if ($token === null) {
            return true;
        }

        return PersonalAccessToken::findToken($token) === null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'confirmed', Password::min(12)->mixedCase()->numbers()->symbols()],
            'preferred_locale' => ['sometimes', 'string', Rule::in(config('wikiworld.supported_locales', ['ar', 'en']))],
        ];
    }
}
