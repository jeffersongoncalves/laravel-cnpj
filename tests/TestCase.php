<?php

namespace JeffersonGoncalves\Cnpj\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use JeffersonGoncalves\Cnpj\CnpjServiceProvider;
use JeffersonGoncalves\PestPluginDatabaseMigrations\LoadsPackageMigrations;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    use LoadsPackageMigrations;
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            CnpjServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', $this->testing_connection());
    }

    /**
     * Defaults to an in-memory SQLite connection for local development; CI
     * (tests.yml) sets CNPJ_TEST_DB_* to run the same suite against real
     * MySQL and PostgreSQL instances too. Deliberately not the plain DB_*
     * names: Orchestra Testbench itself sets DB_CONNECTION=testing.
     *
     * @return array<string, mixed>
     */
    protected function testing_connection(): array
    {
        $driver = env('CNPJ_TEST_DB_DRIVER', 'sqlite');

        if ($driver === 'sqlite') {
            return ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''];
        }

        return [
            'driver' => $driver,
            'host' => env('CNPJ_TEST_DB_HOST', '127.0.0.1'),
            'port' => env('CNPJ_TEST_DB_PORT'),
            'database' => env('CNPJ_TEST_DB_DATABASE', 'testing'),
            'username' => env('CNPJ_TEST_DB_USERNAME', 'root'),
            'password' => env('CNPJ_TEST_DB_PASSWORD', ''),
            'charset' => $driver === 'pgsql' ? 'utf8' : 'utf8mb4',
            'prefix' => '',
        ];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadPackageMigrations(CnpjServiceProvider::class);
    }
}
