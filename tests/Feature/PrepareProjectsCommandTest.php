<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PrepareProjectsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_preparation_seeds_module_permissions_and_starter_plans_without_demo_users(): void
    {
        $this->artisan('projects:prepare')->assertSuccessful();

        $this->assertDatabaseHas('permissions', ['name' => 'manage billing']);
        $this->assertDatabaseHas('permissions', ['name' => 'manage blog']);
        $this->assertDatabaseHas('roles', ['name' => 'admin']);
        $this->assertDatabaseHas('products', ['slug' => 'pro', 'is_visible' => true]);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_preparation_preserves_the_key_and_existing_data_on_reruns(): void
    {
        $user = $this->createUser();
        $key = config('app.key');
        $this->artisan('projects:prepare')->assertSuccessful();

        $this->artisan('projects:prepare')->assertSuccessful();

        $this->assertSame($key, config('app.key'));
        $this->assertModelExists($user);
        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseCount('prices', 4);
    }

    public function test_preparation_generates_a_missing_local_key_once(): void
    {
        $directory = sys_get_temp_dir().'/starter-env-'.bin2hex(random_bytes(8));
        File::makeDirectory($directory);
        File::put($directory.'/.env', "APP_KEY=\n");
        $originalPath = $this->app->environmentPath();
        $originalFile = $this->app->environmentFile();
        $this->app->useEnvironmentPath($directory)->loadEnvironmentFrom('.env');
        config(['app.key' => null]);

        try {
            $this->artisan('projects:prepare')->assertSuccessful();
            $firstEnvironment = File::get($directory.'/.env');

            $this->artisan('projects:prepare')->assertSuccessful();

            $this->assertStringContainsString('APP_KEY=base64:', $firstEnvironment);
            $this->assertSame($firstEnvironment, File::get($directory.'/.env'));
        } finally {
            $this->app->useEnvironmentPath($originalPath)->loadEnvironmentFrom($originalFile);
            File::deleteDirectory($directory);
        }
    }

    public function test_preparation_requires_an_injected_key_in_production(): void
    {
        $this->app->instance('env', 'production');
        config(['app.key' => null]);

        $this->artisan('projects:prepare')
            ->expectsOutputToContain('APP_KEY')
            ->assertFailed();

        $this->assertDatabaseCount('products', 0);
    }
}
