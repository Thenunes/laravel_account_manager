<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Exception;

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

    public function deposit($amount)
    {   
        $this->balance += $amount;
        return $this; 
    }

    public function withdraw($amount)
    {   
        if($this->balance < $amount)
            throw new Exception("Insufficient funds");
            
        $this->balance -= $amount;
        return $this; 
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
