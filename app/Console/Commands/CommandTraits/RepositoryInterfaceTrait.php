<?php

namespace App\Console\Commands\CommandTraits;

trait RepositoryInterfaceTrait
{
    public function createRepositoryInterface(string $modelName): void
    {
        $interfaceContent = $this->getInterfaceContent($modelName);
        $this->createFile($this->interfacesPath, $modelName . 'RepositoryInterface.php', $interfaceContent);

        $this->info($modelName . "RepositoryInterface created!");
    }

    private function getInterfaceContent(string $modelName): string
    {
        return 'INTERFACE FUNC: ' . $modelName . 'RepositoryInterface';
    }
}
