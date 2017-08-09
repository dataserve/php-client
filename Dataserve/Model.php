<?php

namespace Dataserve;

use Exception;

class Model
{    
    protected $dbTable = null;

    protected $alias = null;

    protected $input = null;
    
    protected $result = null;

    protected $meta = null;

    private static $client = null;

    private static $hooks = [];

    public function __construct($input)
    {
        $this->input = $input;
    }   

    public function getInput()
    {
        return $this->input;
    }

    public function setInput($field, $val)
    {
        if (!is_array($this->input)) {
            return;
        }
        
        $this->input[$field] = $val;
    }
    
    public function getResult()
    {
        return $this->result;
    }

    public function getMeta()
    {
        return $this->meta;
    }
    
    public static function __callStatic($command, $arguments)
    {
        if (stripos($command, 'HookPre') !== false || stripos($command, 'HookPost') !== false) {
            if (!isset($arguments[0])) {
                throw new Exception('Missing $hooks value passed into ' . __CLASS__ . '::' . $command);
            }
            return $arguments[0];
        }
        
        if (empty($arguments[0])) {
            throw new Error('Missing input for ' . $command);
        }

        $input = $arguments[0];

        $model = new static($input);

        if ($hooks = self::hooksPre($command)) {
            foreach ($hooks as $hook) {
                $hook($model);
            }
        }

        if (!empty($model->alias)) {
            $model->setInput('alias', $model->alias);
        }
        
        $result = self::getClient()->run($model->dbTable, $command, $model->input);

        $model->result = $result['result'];

        $model->meta = $result['meta'];

        if ($hooks = self::hooksPost($command)) {
            foreach ($hooks as $hook) {
                $hook($model);
            }
        }

        return $model;
    }

    private static function getClient()
    {
        if (!isset(self::$client)) {
            self::$client = new Client;
        }

        return self::$client;
    }

    private static function hooksPre($command)
    {
        if (!isset(self::$hooks[$command])) {
            self::hooksInit($command);
        }
        return self::$hooks[$command]['pre'];
    }

    private static function hooksPost($command)
    {
        if (!isset(self::$hooks[$command])) {
            self::hooksInit($command);
        }
        return self::$hooks[$command]['post'];
    }
    
    private static function hooksInit($command)
    {
        self::$hooks[$command] = [
            'pre' => call_user_func('static::' .  $command . 'HookPre'),
            'post' => call_user_func('static::' .  $command . 'HookPost'),
        ];
    }

}
