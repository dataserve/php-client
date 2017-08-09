<?php

namespace Dataserve;

use Exception;

use Predis\Client as Predis;

class Client
{
    const ALLOWED_COMMANDS = [
        'add' => 'DS_ADD',
        'get' => 'DS_GET',
        'flushCache' => 'DS_FLUSH_CACHE',
        'getCount' => 'DS_GET_COUNT',
        'getMulti' => 'DS_GET_MULTI',
        'log' => 'DS_LOG',
        'lookup' => 'DS_LOOKUP',
        'outputCache' => 'DS_OUTPUT_CACHE',
        'remove' => 'DS_REMOVE',
        'set' => 'DS_SET',
    ];
    
    private static $predis = null;

    public function __construct()
    {
        if (empty($_ENV['DATASERVE_CONNECTION'])) {
            throw new Exception('Missing $_ENV["DATASERVE_CONNECTION"] Path');
        }

        @list($scheme, $host, $port) = explode(',', $_ENV['DATASERVE_CONNECTION']);

        if (!in_array($scheme, ['tcp', 'unix'])) {
            throw new Exception('Invalid scheme in $_ENV["DATASERVE_CONNECTION"]');
        }

        if (!self::$predis) {
            $config = [
                'scheme' => $scheme,
            ];
            
            if ($scheme == 'tcp') {
                $config += [
                    'host' => $host,
                    'port' => $port,
                ];
            } else {
                $config += [
                    'path' => $host,
                ];
            }

            self::$predis = new Predis($config);
        }
    }

    public function run($dbTable, $command, $input)
    {
        if (empty(self::ALLOWED_COMMANDS[$command])) {
            throw new Exception('Invalid command passed: ' . $command);
        }

        $timeStart = microtime(true);

        $result = self::$predis->executeRaw([self::ALLOWED_COMMANDS[$command], $dbTable, json_encode($input)]);

        $result_json = json_decode($result, true);

        if (empty($result_json['status'])) {
            if (isset($result_json['status'])) {
                throw new Exception($result_json['error']);
            }

            throw new Exception('Unknown result: ' . $result);
        }

        $timeRun = microtime(true) - $timeStart;

        return $result_json;
    }

    public function getAllowedCommands()
    {
        return self::ALLOWED_COMMANDS;
    }
    
}
