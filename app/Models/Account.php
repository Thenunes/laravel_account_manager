<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    use HasFactory;

    public $id;
    private $balance;  

    public function __construct($id, $balance) 
    {
        $this->id = $id;
        $this->balance = $balance;
    }

    public function getBalance()
    {
        return $this->balance;
    }
}
