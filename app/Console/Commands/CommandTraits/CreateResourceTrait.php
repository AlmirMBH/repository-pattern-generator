<?php

namespace App\Console\Commands\CommandTraits;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

trait CreateResourceTrait
{
    public function createResource(string $modelName): void
    {
        // TODO: Check if model already exists
        Artisan::call('make:model', ['name' => $modelName]);
        $this->info("Model created: $modelName");

        // TODO: Check if factory already exists
        Artisan::call('make:factory', ['name' => $modelName . 'Factory']);
        $this->info("Factory created: $modelName" . 'Factory');

        $this->createMigrationFileIfNotExists($modelName);
    }

    function createMigrationFileIfNotExists(string $migrationName): void
    {
        $expectedFileName = 'create_' . lcfirst($migrationName) . 's_table.php';

        // Check if a migration file with the given table name already exists
        $migrationExists = collect(File::files(database_path('migrations')))->contains(function ($file) use ($expectedFileName) {
            return str_contains($file->getFilename(), $expectedFileName);
        });

        if (!$migrationExists) {
            Artisan::call('make:migration', ['name' => 'create_' . strtolower($migrationName) . 's_table']);
            $this->info("Migration created: create_" . strtolower($migrationName) . 's_table');
        } else {
            $this->info("Migration for model " . $migrationName . " already exists!");
        }
    }
}
