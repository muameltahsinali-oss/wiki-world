<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\KnowledgeCore\Domain\Enums\WorkflowState;
use Database\Seeders\RelationshipTypeSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class AhlulbaytLaunchSeeder extends Seeder
{
    public function run(): void
    {
        if (DB::table('person_profiles')->where('is_masum', true)->count() >= 14 && DB::table('place_profiles')->count() >= 5) {
            return;
        }

        $this->call(RelationshipTypeSeeder::class);

        $now = now();
        $arabicLanguageId = (int) DB::table('languages')->where('code', 'ar')->value('id');
        $personTypeId = (int) DB::table('entity_types')->where('code', 'person')->value('id');
        $eventTypeId = (int) DB::table('entity_types')->where('code', 'event')->value('id');
        $parentOfTypeId = (int) DB::table('relationship_types')->where('code', 'parent_of')->value('id');

        $masumin = [
            ['title' => 'النبي محمد', 'slug' => 'prophet-muhammad', 'generation' => 0, 'imam_order' => null],
            ['title' => 'السيدة فاطمة الزهراء', 'slug' => 'fatimah-zahra', 'generation' => 1, 'imam_order' => null],
            ['title' => 'الإمام علي', 'slug' => 'imam-ali', 'generation' => 1, 'imam_order' => null],
            ['title' => 'الإمام الحسن', 'slug' => 'imam-hassan', 'generation' => 2, 'imam_order' => 2],
            ['title' => 'الإمام الحسين', 'slug' => 'imam-hussein', 'generation' => 2, 'imam_order' => 3],
            ['title' => 'الإمام علي زين العابدين', 'slug' => 'imam-zayn-al-abidin', 'generation' => 3, 'imam_order' => 4],
            ['title' => 'الإمام محمد الباقر', 'slug' => 'imam-muhammad-al-baqir', 'generation' => 4, 'imam_order' => 5],
            ['title' => 'الإمام جعفر الصادق', 'slug' => 'imam-jafar-al-sadiq', 'generation' => 5, 'imam_order' => 6],
            ['title' => 'الإمام موسى الكاظم', 'slug' => 'imam-musa-al-kazim', 'generation' => 6, 'imam_order' => 7],
            ['title' => 'الإمام علي الرضا', 'slug' => 'imam-ali-al-ridha', 'generation' => 7, 'imam_order' => 8],
            ['title' => 'الإمام محمد الجواد', 'slug' => 'imam-muhammad-al-jawad', 'generation' => 8, 'imam_order' => 9],
            ['title' => 'الإمام علي الهادي', 'slug' => 'imam-ali-al-hadi', 'generation' => 9, 'imam_order' => 10],
            ['title' => 'الإمام الحسن العسكري', 'slug' => 'imam-hassan-al-askari', 'generation' => 10, 'imam_order' => 11],
            ['title' => 'الإمام محمد المهدي', 'slug' => 'imam-muhammad-al-mahdi', 'generation' => 11, 'imam_order' => 12],
        ];

        $entityIds = [];

        foreach ($masumin as $person) {
            $entityId = (int) DB::table('entities')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'entity_type_id' => $personTypeId,
                'workflow_state' => WorkflowState::Published->value,
                'module_flags' => json_encode(['domain' => 'ahlulbayt']),
                'is_indexable' => true,
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('entity_localizations')->insert([
                'entity_id' => $entityId,
                'language_id' => $arabicLanguageId,
                'title' => $person['title'],
                'slug' => $person['slug'],
                'summary' => null,
                'workflow_state' => WorkflowState::Published->value,
                'translation_status' => 'original',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('person_profiles')->insert([
                'entity_id' => $entityId,
                'is_masum' => true,
                'ahlulbayt_generation' => $person['generation'],
                'imam_order' => $person['imam_order'],
                'attributes' => '{}',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $entityIds[$person['slug']] = $entityId;
        }

        $parentLinks = [
            ['prophet-muhammad', 'fatimah-zahra'],
            ['prophet-muhammad', 'imam-ali'],
            ['imam-ali', 'imam-hassan'],
            ['imam-ali', 'imam-hussein'],
            ['imam-hussein', 'imam-zayn-al-abidin'],
            ['imam-zayn-al-abidin', 'imam-muhammad-al-baqir'],
            ['imam-muhammad-al-baqir', 'imam-jafar-al-sadiq'],
            ['imam-jafar-al-sadiq', 'imam-musa-al-kazim'],
            ['imam-musa-al-kazim', 'imam-ali-al-ridha'],
            ['imam-ali-al-ridha', 'imam-muhammad-al-jawad'],
            ['imam-muhammad-al-jawad', 'imam-ali-al-hadi'],
            ['imam-ali-al-hadi', 'imam-hassan-al-askari'],
            ['imam-hassan-al-askari', 'imam-muhammad-al-mahdi'],
        ];

        foreach ($parentLinks as [$parentSlug, $childSlug]) {
            DB::table('entity_relationships')->insert([
                'relationship_type_id' => $parentOfTypeId,
                'source_entity_id' => $entityIds[$parentSlug],
                'target_entity_id' => $entityIds[$childSlug],
                'metadata' => '{}',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $events = [
            [
                'title' => 'غدير خم',
                'slug' => 'ghadir-khumm',
                'summary' => 'حدث غدير خم وتنصيب أمير المؤمنين',
                'start_date' => '632-03-16',
                'start_date_hijri' => '10-12-10',
                'era_code' => 'prophetic',
            ],
            [
                'title' => 'واقعة كربلاء',
                'slug' => 'battle-of-karbala',
                'summary' => 'استشهاد الإمام الحسين وأصحابه في كربلاء',
                'start_date' => '680-10-10',
                'start_date_hijri' => '61-10-10',
                'era_code' => 'umayyad',
            ],
        ];

        foreach ($events as $event) {
            $entityId = (int) DB::table('entities')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'entity_type_id' => $eventTypeId,
                'workflow_state' => WorkflowState::Published->value,
                'module_flags' => json_encode(['domain' => 'ahlulbayt']),
                'is_indexable' => true,
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('entity_localizations')->insert([
                'entity_id' => $entityId,
                'language_id' => $arabicLanguageId,
                'title' => $event['title'],
                'slug' => $event['slug'],
                'summary' => $event['summary'],
                'workflow_state' => WorkflowState::Published->value,
                'translation_status' => 'original',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('event_profiles')->insert([
                'entity_id' => $entityId,
                'start_date_hijri' => $event['start_date_hijri'],
                'start_date_gregorian' => $event['start_date'],
                'date_precision' => 'day',
                'era_code' => $event['era_code'],
                'attributes' => '{}',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // Seed Holy Places
        $placeTypeId = (int) DB::table('entity_types')->where('code', 'place')->value('id');
        $englishLanguageId = (int) DB::table('languages')->where('code', 'en')->value('id');
        $places = [
            [
                'slug' => 'najaf',
                'place_type' => 'city',
                'latitude' => 31.9961,
                'longitude' => 44.3313,
                'modern_country_code' => 'IQ',
                'locales' => [
                    'ar' => [
                        'title' => 'النجف الأشرف',
                        'summary' => 'مدينة النجف الأشرف في العراق، تضم مرقد الإمام علي بن أبي طالب (ع) والحوزة العلمية الدينية المعطاءة.',
                    ],
                    'en' => [
                        'title' => 'Holy Najaf',
                        'summary' => 'The city of Holy Najaf in Iraq, containing the shrine of Imam Ali ibn Abi Talib (a) and the historic Islamic seminary.',
                    ]
                ]
            ],
            [
                'slug' => 'karbala',
                'place_type' => 'city',
                'latitude' => 32.6160,
                'longitude' => 44.0248,
                'modern_country_code' => 'IQ',
                'locales' => [
                    'ar' => [
                        'title' => 'كربلاء المقدسة',
                        'summary' => 'مدينة كربلاء المقدسة في العراق، تضم مرقد الإمام الحسين (ع) وأخيه أبي الفضل العباس (ع)، وشهدت واقعة الطف الأليمة.',
                    ],
                    'en' => [
                        'title' => 'Holy Karbala',
                        'summary' => 'The city of Holy Karbala in Iraq, containing the shrine of Imam Hussein (a) and his brother Al-Abbas (a), and site of the tragedy of Karbala.',
                    ]
                ]
            ],
            [
                'slug' => 'kadhimiya',
                'place_type' => 'city',
                'latitude' => 33.3797,
                'longitude' => 44.3418,
                'modern_country_code' => 'IQ',
                'locales' => [
                    'ar' => [
                        'title' => 'الكاظمية المقدسة',
                        'summary' => 'مدينة الكاظمية في شمال بغداد، تضم مرقد الإمامين الكاظمين: موسى بن جعفر الكاظم ومحمد بن علي الجواد (عليهما السلام).',
                    ],
                    'en' => [
                        'title' => 'Holy Kadhimiya',
                        'summary' => 'A historic district in northern Baghdad, containing the shrine of the Kadhimi Imams: Musa al-Kadhim and Muhammad al-Jawad (a).',
                    ]
                ]
            ],
            [
                'slug' => 'samarra',
                'place_type' => 'city',
                'latitude' => 34.1983,
                'longitude' => 43.8742,
                'modern_country_code' => 'IQ',
                'locales' => [
                    'ar' => [
                        'title' => 'سامراء المقدسة',
                        'summary' => 'مدينة تاريخية في العراق تضم مرقد الإمامين العسكريين: علي الهادي والحسن العسكري (عليهما السلام)، ومقام غيبة الإمام المهدي (عج).',
                    ],
                    'en' => [
                        'title' => 'Holy Samarra',
                        'summary' => 'A historic city in Iraq containing the shrine of the Askari Imams: Ali al-Hadi and Hasan al-Askari (a), and the sanctuary of Imam al-Mahdi (aj).',
                    ]
                ]
            ],
            [
                'slug' => 'kufa-mosque',
                'place_type' => 'mosque',
                'latitude' => 32.0290,
                'longitude' => 44.4011,
                'modern_country_code' => 'IQ',
                'locales' => [
                    'ar' => [
                        'title' => 'مسجد الكوفة المعظم',
                        'summary' => 'من أقدم المساجد في الإسلام، ويقع في مدينة الكوفة بالعراق، وهو مقر خلافة الإمام علي بن أبي طالب (ع) ومكان استشهاده.',
                    ],
                    'en' => [
                        'title' => 'Great Mosque of Kufa',
                        'summary' => 'One of the earliest mosques in Islam, located in Kufa, Iraq, and the former capital seat of Imam Ali (a) and site of his martyrdom.',
                    ]
                ]
            ]
        ];

        foreach ($places as $place) {
            if (DB::table('entity_localizations')->where('slug', $place['slug'])->exists()) {
                continue;
            }

            $entityId = (int) DB::table('entities')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'entity_type_id' => $placeTypeId,
                'workflow_state' => WorkflowState::Published->value,
                'module_flags' => json_encode(['domain' => 'ahlulbayt']),
                'is_indexable' => true,
                'published_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Insert Arabic
            DB::table('entity_localizations')->insert([
                'entity_id' => $entityId,
                'language_id' => $arabicLanguageId,
                'title' => $place['locales']['ar']['title'],
                'slug' => $place['slug'],
                'summary' => $place['locales']['ar']['summary'],
                'workflow_state' => WorkflowState::Published->value,
                'translation_status' => 'original',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Insert English
            if ($englishLanguageId) {
                DB::table('entity_localizations')->insert([
                    'entity_id' => $entityId,
                    'language_id' => $englishLanguageId,
                    'title' => $place['locales']['en']['title'],
                    'slug' => $place['slug'],
                    'summary' => $place['locales']['en']['summary'],
                    'workflow_state' => WorkflowState::Published->value,
                    'translation_status' => 'translated',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('place_profiles')->insert([
                'entity_id' => $entityId,
                'place_type' => $place['place_type'],
                'latitude' => $place['latitude'],
                'longitude' => $place['longitude'],
                'modern_country_code' => $place['modern_country_code'],
                'historical_names' => json_encode([$place['locales']['ar']['title']]),
                'attributes' => '{}',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
