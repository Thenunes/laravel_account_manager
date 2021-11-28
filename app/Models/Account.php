<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Account
{
    const CACHE_KEY = 'accounts';

    public $id;
    private $balance;  

    public function __construct($id, $balance) 
    {   
        $this->id = (int) $id;
        $this->balance = $balance;
    }

    public function getBalance()
    {
        return $this->balance;
    }

    public function save()
    {   
        $accounts = Cache::get(self::CACHE_KEY);
        $accounts[$this->id] = $this;

        Cache::put(self::CACHE_KEY, $accounts);
    }

    public static function find($id)
    {
        $accounts = Cache::get(self::CACHE_KEY);
        return !empty($accounts[$id]) ? $accounts[$id] : false;
    }

    public static function resetAccounts()
    {
        Cache::put(self::CACHE_KEY, []);
    }
}
