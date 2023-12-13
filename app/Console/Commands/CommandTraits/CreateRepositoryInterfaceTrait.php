<?php

namespace App\Console\Commands\CommandTraits;

trait CreateRepositoryInterfaceTrait
{
    public function createRepositoryInterface(string $modelName): void
    {
        $interfaceContent = $this->getInterfaceContent($modelName);
        $this->createFile($this->interfacesPath, $modelName . 'RepositoryInterface.php', $interfaceContent);
    }

    private function getInterfaceContent(string $modelName): string
    {
        return 'INTERFACE FUNC: ' . $modelName . 'RepositoryInterface';
    }
}
