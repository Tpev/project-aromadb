<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = require Application::inferBasePath().'/bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        $this->forceTestingDatabaseFromPhpUnitEnv($app);
        $this->guardAgainstUnsafeTestEnvironment($app);

        return $app;
    }

    private function forceTestingDatabaseFromPhpUnitEnv(Application $app): void
    {
        $allowUnsafe = filter_var((string) env('ALLOW_UNSAFE_TEST_RUN', false), FILTER_VALIDATE_BOOL);

        if ($allowUnsafe) {
            return;
        }

        $forcedConnection = $_SERVER['TEST_DB_CONNECTION']
            ?? $_ENV['TEST_DB_CONNECTION']
            ?? env('TEST_DB_CONNECTION');

        $forcedDatabase = $_SERVER['TEST_DB_DATABASE']
            ?? $_ENV['TEST_DB_DATABASE']
            ?? env('TEST_DB_DATABASE');

        if (is_string($forcedConnection) && $forcedConnection !== '') {
            $app['config']->set('database.default', $forcedConnection);

            if (is_string($forcedDatabase) && $forcedDatabase !== '') {
                $app['config']->set("database.connections.{$forcedConnection}.database", $forcedDatabase);
            }
        }
    }

    private function guardAgainstUnsafeTestEnvironment(Application $app): void
    {
        $allowUnsafe = filter_var((string) env('ALLOW_UNSAFE_TEST_RUN', false), FILTER_VALIDATE_BOOL);

        if ($allowUnsafe) {
            return;
        }

        if ($app->environment('production')) {
            throw new RuntimeException(
                'Refusing to run automated tests in production environment. '.
                'Set ALLOW_UNSAFE_TEST_RUN=true only if you absolutely know what you are doing.'
            );
        }

        $connection = (string) $app['config']->get('database.default');
        $database = (string) ($app['config']->get("database.connections.{$connection}.database") ?? '');

        if ($connection !== 'sqlite') {
            $normalized = strtolower($database);
            $looksLikeTestDb = str_contains($normalized, 'test');

            if (! $looksLikeTestDb) {
                throw new RuntimeException(
                    "Unsafe test database detected: connection [{$connection}] on database [{$database}]. ".
                    "Use a dedicated test DB (name containing 'test') or sqlite for tests. ".
                    "You can bypass with ALLOW_UNSAFE_TEST_RUN=true."
                );
            }
        }
    }
}
