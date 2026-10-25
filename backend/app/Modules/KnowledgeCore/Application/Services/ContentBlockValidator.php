<?php

declare(strict_types=1);

namespace App\Modules\KnowledgeCore\Application\Services;

final class ContentBlockValidator
{
    /**
     * @var list<string>
     */
    private const ALLOWED_TYPES = [
        'heading',
        'paragraph',
        'quote',
        'list',
        'citation',
        'entity_link',
        'image',
        'divider',
    ];

    /**
     * @var list<string>
     */
    private const ALLOWED_HTML_TAGS = [
        'p',
        'strong',
        'em',
        'a',
        'ul',
        'ol',
        'li',
        'blockquote',
    ];

    /**
     * @param  list<array<string, mixed>>  $blocks
     */
    public function validate(array $blocks): void
    {
        foreach ($blocks as $index => $block) {
            if (! is_array($block)) {
                throw new \InvalidArgumentException("Block at index {$index} must be an array.");
            }

            $type = $block['type'] ?? $block['block_type'] ?? null;

            if (! is_string($type) || ! in_array($type, self::ALLOWED_TYPES, true)) {
                throw new \InvalidArgumentException("Invalid block type at index {$index}.");
            }

            if ($type === 'paragraph') {
                $this->validateParagraphContent($block, $index);
            }
        }
    }

    /**
     * @param  array<string, mixed>  $block
     */
    private function validateParagraphContent(array $block, int $index): void
    {
        $content = $block['content'] ?? null;

        if (! is_string($content)) {
            throw new \InvalidArgumentException("Paragraph block at index {$index} must have string content.");
        }

        if ($content === '' || ! str_contains($content, '<')) {
            return;
        }

        $disallowed = $this->findDisallowedTags($content);

        if ($disallowed !== []) {
            throw new \InvalidArgumentException(
                'Paragraph block at index '.$index.' contains disallowed HTML tags: '.implode(', ', $disallowed).'.',
            );
        }
    }

    /**
     * @return list<string>
     */
    private function findDisallowedTags(string $html): array
    {
        if (! preg_match_all('/<\/?([a-zA-Z][a-zA-Z0-9]*)\b[^>]*>/', $html, $matches)) {
            return [];
        }

        $allowed = array_flip(self::ALLOWED_HTML_TAGS);
        $disallowed = [];

        foreach ($matches[1] as $tag) {
            $normalized = strtolower($tag);

            if (! isset($allowed[$normalized])) {
                $disallowed[$normalized] = true;
            }
        }

        return array_keys($disallowed);
    }
}
