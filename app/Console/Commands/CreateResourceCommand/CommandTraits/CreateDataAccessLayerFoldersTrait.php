<?php

namespace App\Console\Commands\CreateResourceCommand\CommandTraits;

use App\Console\Commands\CreateResourceCommand\Constants\Constants;

trait CreateDataAccessLayerFoldersTrait
{
    public function createDataAccessLayerFolders(): void
    {
        $this->createFolderIfNotExists(Constants::REPOSITORY_PATH);
        $this->createFolderIfNotExists(Constants::INTERFACES_PATH);
        $this->createFolderIfNotExists(Constants::SERVICES_PATH);
    }

    private function createFolderIfNotExists(string $path): void
    {
        $fullPath = base_path($path);

        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
            $this->info("Folder created: $path");
        }
    }
}
