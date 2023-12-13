<?php

namespace App\Console\Commands\CommandTraits;

use Illuminate\Support\Facades\Artisan;

trait CreateResourceTrait
{
    public function createResource(string $modelName): void
    {
        Artisan::call('make:model', ['name' => $modelName]);
        $this->info("Model created: $modelName");
        Artisan::call('make:factory', ['name' => $modelName . 'Factory']);
        $this->info("Factory created: $modelName" . 'Factory');
        Artisan::call('make:controller', ['name' => 'Api/' . $modelName . 'Controller']);
        $this->info("Controller created: $modelName" . 'Controller');
        Artisan::call('make:migration', ['name' => 'create_' . strtolower($modelName) . 's_table']);
        $this->info("Migration created: create_" . strtolower($modelName) . 's_table');
    }
}
