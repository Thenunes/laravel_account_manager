<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function balance($account_id)
    {   
        //TODO
        return response()->json(['message' => 'Ok', 'status' => 'Connected']);
    }

     public function event()
    {   
        //TODO
        return response()->json(['message' => 'Ok', 'status' => 'Connected']);
    }
}
