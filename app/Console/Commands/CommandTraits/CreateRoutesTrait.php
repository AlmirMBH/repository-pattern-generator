<?php

namespace App\Console\Commands\CommandTraits;

trait CreateRoutesTrait
{
    public function createRoutes(string $modelName): void
    {
        $routesContent = $this->getRoutesContent($modelName);
//        $this->createFile($this->routesPath, $modelName . 'Routes.php', $routesContent);

        $this->info($modelName . "Routes created!");
    }

    private function getRoutesContent(string $modelName): string
    {
        return 'ROUTES FUNC: ' . $modelName . 'Routes';
    }
}
