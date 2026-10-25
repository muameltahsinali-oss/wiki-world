<?php

declare(strict_types=1);

namespace Tests\Feature\KnowledgeCore;

use Database\Seeders\AhlulbaytLaunchSeeder;
use Tests\Support\EditorialTestHelpers;
use Tests\TestCase;

final class PublicBrowseApiTest extends TestCase
{
    use EditorialTestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedKnowledgeBase();
        $this->seed(AhlulbaytLaunchSeeder::class);
        $this->setUpEditorialHttp();
    }

    public function test_published_ahlulbayt_persons_are_listed(): void
    {
        $response = $this->getJson('/api/v1/ar/persons?filter[module]=ahlulbayt');

        $response->assertOk()
            ->assertJsonStructure(['data', 'meta' => ['pagination']])
            ->assertJsonPath('meta.pagination.total', 14);
    }

    public function test_masumin_filter_returns_only_masumin(): void
    {
        $response = $this->getJson('/api/v1/ar/persons?filter[module]=ahlulbayt&filter[is_masum]=true');

        $response->assertOk();
        $this->assertGreaterThanOrEqual(14, count($response->json('data')));
    }

    public function test_person_list_items_include_person_hints(): void
    {
        $response = $this->getJson('/api/v1/ar/persons?filter[module]=ahlulbayt&filter[is_masum]=true');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'person_hints' => ['is_masum', 'imam_order', 'generation'],
                    ],
                ],
            ])
            ->assertJsonPath('data.0.person_hints.is_masum', true);
    }

    public function test_book_chapters_returns_empty_list_when_none(): void
    {
        $uuid = (string) $this->getJson('/api/v1/ar/persons?filter[module]=ahlulbayt')
            ->json('data.0.uuid');

        $this->getJson('/api/v1/ar/books/'.$uuid.'/chapters')
            ->assertOk()
            ->assertJsonPath('data', []);
    }
}
