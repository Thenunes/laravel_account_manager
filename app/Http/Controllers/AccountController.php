<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;

class AccountController extends Controller
{
    public function balance($account_id)
    {   
        //TODO
        $destination = 100;
        $amount = 10;
        $account = new Account($destination, $amount);
        return response()->json(['message' => $account->getBalance(), 'status' => 'Connected']);
    }

     public function event()
    {   
        //TODO
        return response()->json(['message' => 'Ok', 'status' => 'Connected']);
    }
}
