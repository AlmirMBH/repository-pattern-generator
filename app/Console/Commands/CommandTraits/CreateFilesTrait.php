<?php

namespace App\Console\Commands\CommandTraits;

trait CreateFilesTrait
{
    private function createFile(string $path, string $fileName, string $content): void
    {
        $fullPath = app_path("$path/$fileName");

        if (!file_exists($fullPath)) {
            file_put_contents($fullPath, $content);
            $this->info("File created: $fullPath");
        } else {
            $this->info("File already exists: $fullPath");
        }
    }
}
