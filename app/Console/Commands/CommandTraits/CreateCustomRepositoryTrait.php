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
        return 'CUSTOM REPO FUNC: ' . $modelName . 'Repository';
    }
}
