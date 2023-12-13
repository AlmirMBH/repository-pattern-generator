<?php

namespace App\Console\Commands\CommandTraits;

trait CreateRoutesTrait
{
    public function createRoutes(string $modelName): void
    {
        $routesContent = $this->getRoutesContent($modelName);
//        $this->createFile($this->routesPath, $modelName . 'Routes.php', $routesContent);
    }

    private function getRoutesContent(string $modelName): string
    {
        return 'ROUTES FUNC: ' . $modelName . 'Routes';
    }
}
