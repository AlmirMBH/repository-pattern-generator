<?php

namespace App\Console\Commands\CreateTestsCommand\CommandTraits;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait ModelValidationTrait
{
    public function modelMigrationColumnsExist(string $modelName): bool
    {
        $modelExists = $this->checkIfModelExists($modelName);
        if (! $modelExists) return false;

        $migrationExists = $this->checkIfMigrationExists($modelName);
        if (! $migrationExists) return false;

        $migrationColumnsExist = $this->checkIfMigrationColumnsExist($modelName);
        if (! $migrationColumnsExist) return false;

        return true;
    }

    private function checkIfModelExists(string $modelName): bool
    {
        $this->info("Checking if $modelName model exists...");

        $modelExists = file_exists(app_path("Models/$modelName.php"));

        if (! $modelExists) {
            $this->info("Model $modelName does not exist! Please create it first.");
            $modelExists = false;
        } else {
            $this->info("Model $modelName exists");
        }

        return $modelExists;
    }

    private function checkIfMigrationExists(string $modelName): bool
    {
        $this->info("Checking if model $modelName has a migration...");

        $migrationName = Str::snake(Str::plural($modelName));

        $tableExists = Schema::hasTable($migrationName);

        if (! $tableExists) {
            $this->info("Migration $migrationName does not exist or it has not been migrated." . PHP_EOL .
                              "Please create the migration and columns in it first and then migrate it.");
            $tableExists = false;
        } else {
            $this->info("Migration $migrationName exists");
        }

        return $tableExists;
    }

    private function checkIfMigrationColumnsExist(string $modelName): bool
    {
        $columnsExist = true;
        $migrationName = Str::snake(Str::plural($modelName));

        $this->info("Checking if table $migrationName has columns");

        // we fetch columns and remove the id, created_at, updated_at and deleted_at columns
        $columns = Schema::getColumnListing($migrationName);
        $columns = array_diff($columns, ['id', 'created_at', 'updated_at', 'deleted_at']);

        if (empty($columns)) {
            $this->info("Table $migrationName does not have any columns." . PHP_EOL .
                              "The ID, created_at, updated_at and deleted_at columns do not count." . PHP_EOL .
                              "Please add at least one column (e.g. varchar) to the migration and migrate it first.");
            $columnsExist = false;
        } else {
            $this->info("Table $migrationName has columns");
        }

        return $columnsExist;
    }
}
