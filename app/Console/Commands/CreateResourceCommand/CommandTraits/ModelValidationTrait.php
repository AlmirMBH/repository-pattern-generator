<?php

namespace App\Console\Commands\CreateResourceCommand\CommandTraits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait ModelValidationTrait
{
    private function checkIfModelExists(string $modelName): bool
    {
        $this->info("Checking if $modelName model exists...");

        $modelExists = file_exists(app_path("Models/$modelName.php"));

        if ($modelExists) {
            $this->info("Model $modelName already exists!");
        } else {
            $this->info("Creating $modelName...");
        }

        return $modelExists;
    }

    private function checkIfMigrationExists(string $modelName): bool
    {
        // TODO: Check if migration file exists; then, if table in DB exists ask if user wants to create migration anyway
        $migrationName = Str::snake(Str::plural($modelName));

        $tableExists = Schema::hasTable($migrationName);

        if (! $tableExists) {
            $confirmation = $this->confirm("Prevent duplicates or overrides: Check that '$migrationName' migration is not created and migrated. Create migration anyway?");

            if ($confirmation) {
                $this->info("Creating migration $migrationName...");
            } else {
                $this->info("Migration $migrationName not created.");
                $tableExists = true;
            }
        } else {
            $this->info("Migration $migrationName exists");
        }

        return $tableExists;
    }

    private function checkIfFactoryExist(string $modelName): bool
    {
        $this->info("Checking if {$modelName}Factory exists...");

        $factoryExists = file_exists(database_path("factories/{$modelName}Factory.php"));

        if ($factoryExists) {
            $this->info("{$modelName}Factory already exists!");
        } else {
            $this->info("Creating {$modelName}Factory...");
        }

        return $factoryExists;
    }
}
