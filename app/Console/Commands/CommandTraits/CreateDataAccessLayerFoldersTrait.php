<?php

namespace App\Console\Commands\CommandTraits;

trait CreateDataAccessLayerFoldersTrait
{
    public function createDataAccessLayerFolders(): void
    {
        $this->createFolderIfNotExists($this->repositoryPath);
        $this->createFolderIfNotExists($this->interfacesPath);
        $this->createFolderIfNotExists($this->servicesPath);
    }

    private function createFolderIfNotExists(string $path): void
    {
        $fullPath = app_path($path);

        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);
        }
    }
}
