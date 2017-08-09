<?php

namespace Dataserve\Test;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new \Dotenv\Dotenv(__DIR__ . '/../', '.env_dev');
$dotenv->load();

use Dataserve\Model;

class User extends Model
{
    protected $dbTable = 'user';
    protected $alias = 'u';
    
    protected static function getHookPre($hooks = [])
    {
        $hooks[] = function($model) {
        };
        
        return parent::getHookPre($hooks);
    }

    protected static function getHookPost($hooks = [])
    {
        $hooks[] = function($model) {
        };
        
        return parent::getHookPost($hooks);
    }

}

$user = User::get(10);
print_r($user->getResult());
