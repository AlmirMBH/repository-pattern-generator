<?php

namespace App\Console\Commands\CommandTraits;

trait CreateRepositoryServiceTrait
{
    public function createCustomService(string $modelName): void
    {
        $serviceContent = $this->getServiceContent($modelName);
        $this->createFile($this->servicesPath, $modelName . 'Service.php', $serviceContent);
    }

    private function getServiceContent(string $modelName): string
    {
        return 'SERVICE FUNC: ' . $modelName . 'Service';
    }


}
