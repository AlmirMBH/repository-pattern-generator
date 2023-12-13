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

    }
}
