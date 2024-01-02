<?php

namespace App\Console\Commands\CreateResourceCommand\CommandTraits;

use App\Console\Commands\CreateResourceCommand\Constants\Constants;
use Illuminate\Support\Str;

trait ClassesToCreateDataTrait
{
    private function getDataToCreateClasses(string $modelName, bool $includeRepository): array
    {
        $controllerStubFile = $includeRepository ? Constants::REPOSITORY_CONTROLLER_STUB : Constants::CONTROLLER_STUB;
        $existingRepositoryServiceProvider = Constants::REPOSITORY_SERVICE_PROVIDER_PATH . '/' . Constants::REPOSITORY_SERVICE_PROVIDER_FILE_NAME;
        $routeGroupController = ucfirst($modelName) . "Controller";
        $routesPrefix = str_replace('_', '-', Str::snake(Str::plural($modelName)));
        $paramModelName = lcfirst($modelName);
        $routeNamePlural = Str::plural($modelName);

        $baseRepositoryInterface = [
            'name' => 'BaseRepositoryInterface.php',
            'modelName' => ucfirst($modelName),
            'path' => Constants::INTERFACES_PATH,
            'stubFile' => Constants::BASE_REPOSITORY_INTERFACE_STUB,
            'replacements' => []
        ];

        $customRepositoryInterface = [
            'name' => $modelName . 'RepositoryInterface.php',
            'modelName' => ucfirst($modelName),
            'path' => Constants::INTERFACES_PATH,
            'stubFile' => Constants::CUSTOM_REPOSITORY_INTERFACE_STUB,
            'replacements' => [
                '{{ modelName }}' => ucfirst($modelName),
            ]
        ];

        $baseRepository = [
            'name' => 'BaseRepository.php',
            'modelName' => ucfirst($modelName),
            'path' => Constants::REPOSITORY_PATH,
            'stubFile' => Constants::BASE_REPOSITORY_STUB,
            'replacements' => []
        ];

        $customRepository = [
            'name' => $modelName . 'Repository.php',
            'modelName' => ucfirst($modelName),
            'path' => Constants::REPOSITORY_PATH,
            'stubFile' => Constants::CUSTOM_REPOSITORY_STUB,
            'replacements' => [
                '{{ modelName }}' => ucfirst($modelName)
            ]
        ];

        $repositoryService = [
            'name' => $modelName . 'Service.php',
            'modelName' => ucfirst($modelName),
            'path' => Constants::SERVICES_PATH,
            'stubFile' => Constants::REPOSITORY_SERVICE_STUB,
            'replacements' => [
                '{{ modelName }}' => ucfirst($modelName),
                '{{ repositoryName }}' => lcfirst($modelName) . 'Repository'
            ]
        ];

        $controller = [
            'name' => $modelName . 'Controller.php',
            'modelName' => ucfirst($modelName),
            'path' => Constants::CONTROLLER_PATH,
            'stubFile' => $controllerStubFile,
            'replacements' => [
                '{{ modelName }}' => ucfirst($modelName),
                '{{ serviceName }}' => lcfirst($modelName) . 'Service',
            ]
        ];

        $routes = [
            'name' => Constants::EXISTING_ROUTES_FILE_NAME,
            'modelName' => ucfirst($modelName),
            'path' => Constants::ROUTES_PATH,
            'stubFile' => Constants::ROUTES_PATH . '/' . Constants::EXISTING_ROUTES_FILE_NAME,
            'replacements' => [],
            'append' => "Route::controller(App\Http\Controllers\Api\\" . $routeGroupController . "::class)->group(function () {
     Route::prefix('$routesPrefix')->group(function () {
        Route::get('/', 'index')->name('get$routeNamePlural');
        Route::post('/', 'create')->name('create$modelName');
        Route::get('/{{$paramModelName}Id}', 'show')->name('get$modelName');
        Route::put('/{{$paramModelName}Id}', 'update')->name('update$modelName');
        Route::delete('/{{$paramModelName}Id}', 'delete')->name('delete$modelName');
    });
});",
        ];

        $repositoryServiceProvider = [
            'name' => Constants::REPOSITORY_SERVICE_PROVIDER_FILE_NAME,
            'modelName' => $modelName,
            'path' => Constants::REPOSITORY_SERVICE_PROVIDER_PATH,
            'stubFile' => file_exists(base_path($existingRepositoryServiceProvider))
                ? $existingRepositoryServiceProvider
                : Constants::REPOSITORY_SERVICE_PROVIDER_STUB_PATH,
            'replacements' => [
                '// {{ interfaceAndRepositoryImports }}' =>
                'use App\DataAccessLayer\Interfaces\\' . $modelName . 'RepositoryInterface;' . PHP_EOL .
                'use App\DataAccessLayer\Repositories\\' . $modelName . 'Repository;' . PHP_EOL .
                '// {{ interfaceAndRepositoryImports }}',
                '// {{ interfaceRepositoryBinding }}' =>
                    '$this->app->bind(' . $modelName . 'RepositoryInterface::class,' . $modelName . 'Repository::class);' . PHP_EOL .
                    '        // {{ interfaceRepositoryBinding }}'
            ]
        ];

        $data = [
            $baseRepositoryInterface,
            $customRepositoryInterface,
            $baseRepository,
            $customRepository,
            $repositoryService,
            $repositoryServiceProvider,
            $routes,
            $controller
        ];

        return $includeRepository
            ? array_slice($data, 0, 8)
            : array_slice($data, 6, 2);
    }
}
