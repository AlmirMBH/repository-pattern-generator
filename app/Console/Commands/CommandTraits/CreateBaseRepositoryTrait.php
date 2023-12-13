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
        $stubPath = base_path('stubs/base_repository.stub');
        return file_get_contents($stubPath);
    }
}
