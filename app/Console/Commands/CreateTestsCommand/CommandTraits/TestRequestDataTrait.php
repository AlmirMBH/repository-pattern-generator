<?php

namespace App\Console\Commands\CreateTestsCommand\CommandTraits;

use Illuminate\Support\Facades\Schema;

trait TestRequestDataTrait
{
    public function getTestRequestData(string $modelName): array
    {
        $createRequestData = $this->getRequestData(modelName: $modelName, action: 1);
        $expectedCreateResponseData = $this->getExpectedResponseData(requestData: $createRequestData);

        $createModelSequenceRequestData = $this->getRequestData(modelName: $modelName, numberOfModels: 2);
        $expectedModelSequenceResponseData = $this->getExpectedResponseData(requestData: $createModelSequenceRequestData);

        $updateRequestData = $this->getRequestData(modelName: $modelName, action: 'update');
        $expectedUpdateResponseData = $updateRequestData;

        return [
            $createRequestData,
            $expectedCreateResponseData,
            $createModelSequenceRequestData,
            $expectedModelSequenceResponseData,
            $updateRequestData,
            $expectedUpdateResponseData,
        ];
    }

    private function getRequestData(string $modelName, string $action = '', int $numberOfModels = 1): array
    {
        $requestData = [];
        $updateStringFlag = '';
        $updateIntFlag = 0;

        if ($action === 'update') {
            $updateStringFlag = '-updated';
            $updateIntFlag = 1;
        }

        $columns = $this->getModelColumnsAndTypes($modelName);

        $predefinedColumnValues = [
            'char' => 'A',
            'varchar' => 'test',
            'text' => 'test',
            'longtext' => 'This is a long text!',
            'tinyint' => 1,
            'int' => 1,
            'bigint' => 1,
            'double' => 1.1,
            'decimal' => 1.1,
            'json' => 'json data',
            'blob' => '\x48\x65\x6C\x6C\x6F\x20\x57\x6F\x72\x6C\x64',
            'date' => '2021-01-01',
            'datetime' => '2021-01-01 00:00:00',
            'timestamp' => '2021-01-01 00:00:00',
            'enum' => 'test',
        ];

        foreach ($columns as $column) {
            $requestData[$column['column_name']] = match ($column['type']) {
                'varchar', 'text', 'longtext' => $predefinedColumnValues[$column['type']] . $updateStringFlag,
                'tinyint', 'int', 'bigint', 'double', 'decimal' => $predefinedColumnValues[$column['type']] + $updateIntFlag,
                'char', 'blob', 'date', 'datetime', 'timestamp' => $predefinedColumnValues[$column['type']],
                'enum' => $predefinedColumnValues[$column['type']], // TODO: Fetch enum values; add json data type
                default => '',
            };
        }

        // Create copies of the array
        if ($numberOfModels > 1) {
            return array_fill(0, $numberOfModels, $requestData);
        }

        return $requestData;
    }

    private function getExpectedResponseData(array $requestData, string $action = ''): array
    {
        $updateStringFlag = '';
        $updateIntFlag = 0;

        if ($action === 'update') {
            $updateStringFlag = '-updated';
            $updateIntFlag = 1;
        }

        foreach ($requestData as $key => $value) {
            if (is_array($value)) {
                $requestData[$key]['id'] = $key + 1; // id must start from 1
            } else {
                if (is_string($value)) {
                    $value .= $updateStringFlag;
                }

                if (is_int($value)) {
                    $value += $updateIntFlag;
                }

                $requestData[$key] = $value;
                $requestData['id'] = 1; // if not matrix, only one instance created and its id must be 1
            }
        }

        return $requestData;
    }

    private function getModelColumnsAndTypes(string $modelName): array
    {
        $columnsWithTypes = [];
        $modelColumnsNotRequiredForTestRequests = ['id', 'created_at', 'updated_at', 'deleted_at'];
        $modelClassName = '\App\Models\\' . $modelName;
        $user = new $modelClassName();

        $table = $user->getTable();
        $columns = Schema::getColumnListing($table);

        foreach ($columns as $column) {
            if (! in_array($column, $modelColumnsNotRequiredForTestRequests)) {
                $columnsWithTypes[] = [
                    'column_name' => $column,
                    'type' => Schema::getColumnType($table, $column),
                ];
            }
        }

        return $columnsWithTypes;
    }

    private function convertArrayToString(array $array): string
    {
        // the arrays are converted to strings, so that they can replace placeholders in stubs
        $arrayConverted = implode(', ', array_map(function ($key, $value) use ($array) {
            return is_array($value)
                ? $this->convertArrayToString($value)
                : "'$key' => " . (is_string($value) ? "'$value'" : $value);

        }, array_keys($array), $array));

        $arrayConverted = str_replace(', ', ",\n", $arrayConverted);

        return "[\n$arrayConverted\n]";
    }
}
