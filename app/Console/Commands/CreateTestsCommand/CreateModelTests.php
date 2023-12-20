<?php

namespace App\Console\Commands\CreateTestsCommand;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateModelTests extends Command
{
    use CommandTraits\ModelValidationTrait;
    use CommandTraits\TestRequestDataTrait;


    protected $signature = 'make:tests {name : The name of the Eloquent model}';
    protected $description = 'Command description';

    private string $testPath = 'tests/Feature';

    public function handle(): void
    {
        // TODO: Format arrays in the test (indentation)
        // TODO: mass assignment columns must be specified in the model; otherwise tests will fail
        // TODO: Explain what column types can be tested
        // TODO: add in the README.md file that the testing DB needs to be set in phpunit.xml and .env.testing
        // TODO: explain that some code might be the same in different commands; the purpose is easy copying and pasting
        // TODO: only what you need
        $modelName = ucfirst($this->argument('name'));

        $modelMigrationColumnsExist = $this->modelMigrationColumnsExist($modelName);

        if (! $modelMigrationColumnsExist) {
            return;
        }

        [
            $createRequestData,
            $expectedCreateResponseData,
            $createModelSequenceRequestData,
            $expectedModelSequenceResponseData,
            $updateRequestData,
            $expectedUpdateResponseData,
        ] = $this->getTestRequestData($modelName);

        $testData = [
            'class' => ucfirst($modelName) . 'ApiTest.php',
            'modelName' => ucfirst($modelName),
            'path' => $this->testPath,
            'stubFile' => 'app/Console/Commands/CreateTestsCommand/TestTemplates/feature_test.stub',
            'replacements' => [
                '{{ modelName }}' => ucfirst($modelName),
                '{{ modelVariableName }}' => lcfirst($modelName),
                '{{ modelNamePlural }}' => Str::plural($modelName),

                '{{ createRequestData }}' => $this->convertArrayToString($createRequestData),
                '{{ expectedCreateResponseData }}' => $this->convertArrayToString($expectedCreateResponseData),

                '{{ modelSequence }}' => $this->convertArrayToString($createModelSequenceRequestData),
                '{{ expectedModelSequenceResponseData }}' => $this->convertArrayToString($expectedModelSequenceResponseData),

                '{{ updateRequestData }}' => $this->convertArrayToString($updateRequestData),
                '{{ expectedUpdateResponseData }}' => $this->convertArrayToString($expectedUpdateResponseData),
                '{{ databaseName }}' => Str::snake(Str::plural($modelName)),
            ]
        ];

        $this->createTest($testData);
    }

    private function createTest(array $data): void
    {
        $fileToCreateContent = $this->getFileContent($data);
        $this->createFile($data['path'], $data['class'], $fileToCreateContent);
    }

    private function getFileContent(array $data): string
    {
        $stubPath = base_path($data['stubFile']);
        $stubContents = file_get_contents($stubPath);

        return str_replace(array_keys($data['replacements']), array_values($data['replacements']), $stubContents);
    }

    private function createFile(string $path, string $fileName, string $content): void
    {
        $basePath = "$path/$fileName";

        if (! file_exists(base_path($basePath))) {
            file_put_contents($basePath, $content);
            $this->info("$fileName created!");
        } else {
            $this->info($fileName . " already exists!");
        }
    }
}
