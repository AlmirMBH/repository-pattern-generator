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
        $stubPath = base_path('app/Console/ClassTemplates/base_repository.stub');
        return file_get_contents($stubPath);
    }
}
