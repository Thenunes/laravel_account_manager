<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Account;

class AccountController extends Controller
{
    const EVENT_DEPOSIT = 'deposit';
    const EVENT_WITHDRAW = 'withdraw';
    const EVENT_TRANSFER = 'transfer';

    public function reset()
    {   
        Account::resetAccounts();
        return response()->json(["message" => "Ok"], Response::HTTP_OK);
    }

    public function balance(Request $request)
    {   
        $account_id = $request->input('account_id');
        if(empty($account_id))
            return response()->json(["message" => "account_id cannot be empty."], Response::HTTP_NOT_FOUND);

        $account = Account::find($account_id);
        if(!$account)
            return response()->json(["message" => "Account not found."], Response::HTTP_NOT_FOUND);

        return response()->json([
            'destination' => [
                'id' => $account->id,
                'balance' => $account->getBalance()
            ]
        ], Response::HTTP_OK);
    }

    private function eventDeposit(Request $request)
    {
        $destination = $request->input('destination');
        $amount = $request->input('amount');

        if(empty($destination))
            return response()->json(["message" => "destination cannot be empty."], Response::HTTP_NOT_FOUND);

        if(!is_numeric($destination))
            return response()->json(["message" => "destination must be a integer."], Response::HTTP_NOT_FOUND);

        if(empty($amount))
            return response()->json(["message" => "amount cannot be empty."], Response::HTTP_NOT_FOUND);

        if($amount <= 0)
            return response()->json(["message" => "amount must be greater then 0."], Response::HTTP_NOT_FOUND);;

        //TODO create if it is non-existing account
        $account = new Account($destination, $amount);
        $account->save();

        return response()->json([
            'destination' => [
                'id' => $account->id,
                'balance' => $account->getBalance()
            ]
        ], Response::HTTP_OK);
    }

    private function eventWithdraw()
    {
        //TODO
    }

    private function eventTransfer()
    {
        //TODO
    }

    public function event(Request $request)
    {   
        $type = $request->input('type');

        if(empty($type))
            return response()->json(["message" => 'type cannot be empty.'], Response::HTTP_NOT_FOUND);

        switch ($type) 
        {
            case self::EVENT_DEPOSIT: return $this->eventDeposit($request); break;
            case self::EVENT_DEPOSIT: return $this->eventWithdraw($request); break;
            case self::EVENT_DEPOSIT: return $this->eventTransfer($request); break;
            
            default: 
                return response()->json(["message" => 'type not found.'], Response::HTTP_NOT_FOUND);
                break;
        }
    }
}
