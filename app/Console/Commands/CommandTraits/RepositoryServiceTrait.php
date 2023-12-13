<?php

namespace App\Console\Commands\CommandTraits;

trait RepositoryServiceTrait
{
    public function createCustomService(string $modelName): void
    {
        $serviceContent = $this->getServiceContent($modelName);
        $this->createFile($this->servicesPath, $modelName . 'Service.php', $serviceContent);


        $this->info($modelName . "Service created!");
    }

    private function getServiceContent(string $modelName): string
    {
        return 'SERVICE FUNC: ' . $modelName . 'Service';
    }


}
