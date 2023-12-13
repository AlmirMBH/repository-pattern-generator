<?php

namespace App\Console\Commands\CommandTraits;

trait CreateBaseRepositoryInterfaceTrait
{
    public function createBaseRepositoryInterface(): void
    {
        $interfaceContent = $this->getBaseRepositoryInterfaceContent();
        $this->createFile($this->interfacesPath, 'BaseRepositoryInterface.php', $interfaceContent);
    }

    private function getBaseRepositoryInterfaceContent(): string
    {
        $stubPath = base_path('stubs/base_repository_interface.stub');
        return file_get_contents($stubPath);
    }
}
