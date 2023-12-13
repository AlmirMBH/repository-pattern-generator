<?php

namespace App\Console\Commands\CommandTraits;

trait CreateControllerTrait
{

    public function createController(string $modelName): void
    {
        $baseRepositoryContent = $this->getControllerContent($modelName);
        $this->createFile($this->controllerPath, $modelName . 'Controller.php', $baseRepositoryContent);
    }

    private function getControllerContent(string $modelName): string
    {
        $stubPath = base_path('app/Console/ClassTemplates/controller.stub');
        $stubContents = file_get_contents($stubPath);

        $replacements = [
            '{{ modelName }}' => ucfirst($modelName),
            '{{ serviceName }}' => lcfirst($modelName) . 'Service',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stubContents);
    }
}
