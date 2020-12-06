<?php

namespace App\Traits;

use App\Models\Contractor;
use App\Models\HiringOrganization;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Log;
use Aws\CloudWatchLogs\CloudWatchLogsClient;
use Maxbanton\Cwh\Handler\CloudWatch;
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
trait LoggerTrait
{
    public function logMessage($message)
    {
        $m = $message;
    }

    public function logPerformance($performance) 
    {
        $p = $performance;
    }
}