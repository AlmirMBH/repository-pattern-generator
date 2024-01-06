<?php

namespace App\Console\Commands\CreateQueryLoggerCommand\Constants;

class Constants
{
    public const QUERY_LOGGER_ENVIRONMENT = 'QUERY_LOGGER_ENVIRONMENT';
    public const LOW_PERFORMANCE_QUERY_MEMORY = 'LOW_PERFORMANCE_QUERY_MEMORY';
    public const LOW_PERFORMANCE_QUERY_TIME = 'LOW_PERFORMANCE_QUERY_TIME';
    public const MID_PERFORMANCE_QUERY_MEMORY = 'MID_PERFORMANCE_QUERY_MEMORY';
    public const MID_PERFORMANCE_QUERY_TIME = 'MID_PERFORMANCE_QUERY_TIME';
    public const HIGH_PERFORMANCE_QUERY_MEMORY = 'HIGH_PERFORMANCE_QUERY_MEMORY';
    public const HIGH_PERFORMANCE_QUERY_TIME = 'HIGH_PERFORMANCE_QUERY_TIME';
    public const QUERY_LOG_FILE_NAME = 'QUERY_LOG_FILE_NAME';
    public const KEEP_QUERY_LOGS_DAYS = 'KEEP_QUERY_LOGS_DAYS';

    public const QUERY_LOGGER_ENVIRONMENT_KEY = 'query_logger_environment';
    public const QUERY_LOG_FILE_NAME_KEY = 'query_logs_file_name';
    public const KEEP_QUERY_LOGS_DAYS_KEY = 'keep_query_logs_days';
    public const HIGH_PERFORMANCE_QUERY_MEMORY_KEY = 'high_performance_query_memory';
    public const HIGH_PERFORMANCE_QUERY_TIME_KEY = 'high_performance_query_time';
    public const MID_PERFORMANCE_QUERY_MEMORY_KEY = 'mid_performance_query_memory';
    public const MID_PERFORMANCE_QUERY_TIME_KEY = 'mid_performance_query_time';
    public const LOW_PERFORMANCE_QUERY_MEMORY_KEY = 'low_performance_query_memory';
    public const LOW_PERFORMANCE_QUERY_TIME_KEY = 'low_performance_query_time';
}
