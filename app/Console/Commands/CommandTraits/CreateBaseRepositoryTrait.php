<?php

namespace App\Console\Commands\CommandTraits;

trait CreateBaseRepositoryTrait
{

    public function createBaseRepository(): void
    {
        $baseRepositoryContent = $this->getBaseRepositoryContent();
        $this->createFile($this->repositoryPath, 'BaseRepository.php', $baseRepositoryContent);
    }

    private function getBaseRepositoryContent(): string
    {
        return 'BASE REPO FUNC';
    }
}
