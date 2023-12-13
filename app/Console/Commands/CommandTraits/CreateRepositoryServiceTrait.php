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
        $stubPath = base_path('app/Console/ClassTemplates/service.stub');
        $stubContents = file_get_contents($stubPath);

        $replacements = [
            '{{ modelName }}' => ucfirst($modelName),
            '{{ repositoryName }}' => lcfirst($modelName) . 'Repository'
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stubContents);
    }



}
