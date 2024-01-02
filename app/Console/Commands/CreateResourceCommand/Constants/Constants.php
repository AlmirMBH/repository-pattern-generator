<?php

namespace App\Console\Commands\CreateResourceCommand\Constants;

class Constants
{
    /**
     * Folder names
     */
    public const CONTROLLER_PATH = 'app/Http/Controllers/Api';
    public const REPOSITORY_PATH = 'app/DataAccessLayer/Repositories';
    public const SERVICES_PATH = 'app/DataAccessLayer/Services';
    public const INTERFACES_PATH = 'app/DataAccessLayer/Interfaces';
    public const REPOSITORY_SERVICE_PROVIDER_PATH = 'app/Providers';
    public const ROUTES_PATH = 'routes';

    /**
     * Stub files
     */
    public const BASE_REPOSITORY_INTERFACE_STUB = 'app/Console/Commands/CreateResourceCommand/ClassTemplates/base_repository_interface.stub';
    public const CUSTOM_REPOSITORY_INTERFACE_STUB = 'app/Console/Commands/CreateResourceCommand/ClassTemplates/custom_repository_interface.stub';
    public const BASE_REPOSITORY_STUB = 'app/Console/Commands/CreateResourceCommand/ClassTemplates/base_repository.stub';
    public const CUSTOM_REPOSITORY_STUB = 'app/Console/Commands/CreateResourceCommand/ClassTemplates/repository.stub';
    public const REPOSITORY_SERVICE_STUB = 'app/Console/Commands/CreateResourceCommand/ClassTemplates/service.stub';
    public const REPOSITORY_CONTROLLER_STUB = 'app/Console/Commands/CreateResourceCommand/ClassTemplates/repository_controller.stub';
    public const CONTROLLER_STUB = 'app/Console/Commands/CreateResourceCommand/ClassTemplates/controller.stub';
    public const REPOSITORY_SERVICE_PROVIDER_STUB_PATH = 'app/Console/Commands/CreateResourceCommand/ClassTemplates/repository_service_provider.stub';

    /**
     * File names
     */
    public const EXISTING_ROUTES_FILE_NAME = 'api.php';
    public const REPOSITORY_SERVICE_PROVIDER_FILE_NAME = 'RepositoryServiceProvider.php';

    /**
     * Console messages
     */
    public const RESOURCE_CREATED = 'Resource created!' . PHP_EOL .
        'Add columns to the model, migration and in the factory.' . PHP_EOL .
        'Add factory call in the DatabaseSeeder.php file.' . PHP_EOL .
        'Run php artisan migrate:fresh --seed to create the table and seed it with data.' .PHP_EOL .
        'Use an API tool like Postman to test the endpoints.';
    public const REPOSITORY_SERVICE_PROVIDER_ADDED_TO_CONFIG = 'RepositoryServiceProvider added to config/app.php!';
    public const PROVIDERS_ARRAY_NOT_FOUND_IN_CONFIG = "Error: Unable to find 'providers' array in the configuration file.";
    public const CACHE_CLEARED = 'Cache cleared!';
    public const COMPOSER_DUMP_AUTO_LOADED = 'Composer dump-auto-loaded!';
}
