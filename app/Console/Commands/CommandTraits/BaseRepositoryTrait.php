<?php

namespace App\Console\Commands\CommandTraits;

trait BaseRepositoryTrait
{

    public function createBaseRepository(): void
    {
        $baseRepositoryContent = $this->getBaseRepositoryContent();
        $this->createFile($this->repositoryPath, 'BaseRepositoryTrait.php', $baseRepositoryContent);

        $this->info("BaseRepositoryTrait created!");
    }

    private function getBaseRepositoryContent(): string
    {
        return 'BASE REPO FUNC';
    }
}
