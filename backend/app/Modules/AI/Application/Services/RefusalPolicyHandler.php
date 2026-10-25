<?php

declare(strict_types=1);

namespace App\Modules\AI\Application\Services;

final class RefusalPolicyHandler
{
    private const PATTERNS = [
        'فتوى',
        'فتوي',
        'fatwa',
        'حكم شرعي',
        'is it haram',
        'is this haram',
        'حلال أم حرام',
        'حلال او حرام',
        'هل يجوز',
        'ما حكم',
        'شرعيًا',
        'شرعيا',
        'give me a ruling',
        'religious ruling',
    ];

    public const DISCLAIMER = 'This is educational content, not a religious ruling.';

    public function shouldRefuse(string $message): bool
    {
        $normalized = mb_strtolower($message);

        foreach (self::PATTERNS as $pattern) {
            if (mb_stripos($normalized, mb_strtolower($pattern)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{answer: string, citations: list<array<string, mixed>>, disclaimer: string, refused: true}
     */
    public function refusalResponse(): array
    {
        return [
            'answer' => 'I cannot provide fatwas or religious rulings. Please consult a qualified scholar. I can share educational historical and contextual information from published corpus articles instead.',
            'citations' => [],
            'disclaimer' => self::DISCLAIMER,
            'refused' => true,
        ];
    }
}
