<?php

namespace App\Console\Commands\CommandTraits;

trait CreateCustomRepositoryInterfaceTrait
{
    public function createCustomRepositoryInterface(string $modelName): void
    {
        $customInterfaceContent = $this->getCustomRepositoryInterfaceContent($modelName);
        $this->createFile($this->interfacesPath, $modelName . 'RepositoryInterface.php', $customInterfaceContent);
    }

    private function getCustomRepositoryInterfaceContent(string $modelName): string
    {
        $stubPath = base_path('stubs/custom_repository_interface.stub');
        $stubContents = file_get_contents($stubPath);

        $replacements = [
            '{{ modelName }}' => ucfirst($modelName),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stubContents);
    }
}
