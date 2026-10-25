<?php

declare(strict_types=1);

namespace Tests\Architecture;

use Tests\TestCase;

final class ModuleBoundaryTest extends TestCase
{
    public function test_domain_layers_do_not_import_http(): void
    {
        $domainPaths = glob(base_path('app/Modules/*/Domain/**/*.php'));

        $this->assertNotEmpty($domainPaths);

        foreach ($domainPaths as $file) {
            $contents = file_get_contents($file);

            if ($contents === false) {
                continue;
            }

            $this->assertStringNotContainsString('Illuminate\\Http\\', $contents, "HTTP import in: {$file}");
            $this->assertStringNotContainsString('App\\Http\\', $contents, "HTTP import in: {$file}");
        }
    }

    public function test_domain_layers_do_not_import_infrastructure(): void
    {
        $domainPaths = glob(base_path('app/Modules/*/Domain/**/*.php'));

        foreach ($domainPaths as $file) {
            $contents = file_get_contents($file);

            if ($contents === false) {
                continue;
            }

            $this->assertStringNotContainsString('Infrastructure\\', $contents, "Infrastructure import in: {$file}");
            $this->assertStringNotContainsString('Illuminate\\Database\\', $contents, "Database import in: {$file}");
        }
    }
}
