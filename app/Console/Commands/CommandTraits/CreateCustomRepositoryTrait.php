<?php

namespace App\Console\Commands\CommandTraits;

trait CreateCustomRepositoryTrait
{
    public function createCustomRepository(string $modelName): void
    {
        $customRepositoryContent = $this->getCustomRepositoryContent($modelName);
        $this->createFile($this->repositoryPath, $modelName . 'Repository.php', $customRepositoryContent);
    }

    private function getCustomRepositoryContent(string $modelName): string
    {
        $stubPath = base_path('app/Console/ClassTemplates/repository.stub');
        $stubContents = file_get_contents($stubPath);

        $replacements = [
            '{{ modelName }}' => ucfirst($modelName),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $stubContents);
    }
}
