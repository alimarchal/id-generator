<?php

namespace Alimarchal\IdGenerator\Tests;

use Alimarchal\IdGenerator\IdGeneratorServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn(string $modelName) => 'Alimarchal\\IdGenerator\\Database\\Factories\\' . class_basename($modelName) . 'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            IdGeneratorServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        // Run the migrations
        $migration = include __DIR__ . '/../database/migrations/2024_01_01_000000_create_id_prefixes_table.php';
        $migration->up();

        // Create test tables
        $app['db']->connection()->getSchemaBuilder()->create('test_invoices', function ($table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->timestamps();
        });

        $app['db']->connection()->getSchemaBuilder()->create('test_orders', function ($table) {
            $table->id();
            $table->string('order_no')->unique();
            $table->timestamps();
        });
    }
}