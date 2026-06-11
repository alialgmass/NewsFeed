<?php

namespace Modules\Gateway\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorsMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_cors_config_is_published(): void
    {
        $allowedOrigins = config('cors.allowed_origins');

        $this->assertIsArray($allowedOrigins);
        $this->assertContains('*', $allowedOrigins);
    }

    public function test_cors_allows_api_paths(): void
    {
        $paths = config('cors.paths');

        $this->assertIsArray($paths);
        $this->assertContains('api/*', $paths);
    }
}
