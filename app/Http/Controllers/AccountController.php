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
        $accountId = $request->input('account_id');
        if(empty($accountId))
            return response()->json(["message" => "account_id cannot be empty."], Response::HTTP_NOT_FOUND);

        $account = Account::find($accountId);
        if(!$account)
            return response()->json(["message" => "Account not found."], Response::HTTP_NOT_FOUND);

        return response()->json($account->getBalance(), Response::HTTP_OK);
    }

    private function eventDeposit(Request $request)
    {
        $destination = $request->input('destination');
        $amount = $request->input('amount');

        // Tests
        if(empty($destination))
            return response()->json(["message" => "destination cannot be empty."], Response::HTTP_NOT_FOUND);

        if(!is_numeric($destination))
            return response()->json(["message" => "destination must be a integer."], Response::HTTP_NOT_FOUND);

        if(empty($amount))
            return response()->json(["message" => "amount cannot be empty."], Response::HTTP_NOT_FOUND);

        if($amount <= 0)
            return response()->json(["message" => "amount must be greater then 0."], Response::HTTP_NOT_FOUND);

        // Logic
        try 
        {
            $account = Account::find($destination);
            if($account)
                $account->deposit($amount);
            else
                $account = new Account($destination, $amount);

            $account->save();
        } 
        catch(\Exception $e)
        {
            return response()->json(["message" => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        // Response
        return response()->json([
            'destination' => [
                'id' => $account->id,
                'balance' => $account->getBalance()
            ]
        ], Response::HTTP_OK);
    }

    private function eventWithdraw(Request $request)
    {
        $origin = $request->input('origin');
        $amount = $request->input('amount');

        // Tests
        if(empty($origin))
            return response()->json(["message" => "origin cannot be empty."], Response::HTTP_NOT_FOUND);

        if(!is_numeric($origin))
            return response()->json(["message" => "origin must be a integer."], Response::HTTP_NOT_FOUND);

        if(empty($amount))
            return response()->json(["message" => "amount cannot be empty."], Response::HTTP_NOT_FOUND);

        if($amount <= 0)
            return response()->json(["message" => "amount must be greater then 0."], Response::HTTP_NOT_FOUND);

        // Logic
        try 
        {
            $account = Account::find($origin);
            if(!$account)
                return response()->json(["message" => "Account not found."], Response::HTTP_NOT_FOUND);

            $account->withdraw($amount);
            $account->save();
        } 
        catch(\Exception $e)
        {
            return response()->json(["message" => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
        
        // Response
        return response()->json([
            'origin' => [
                'id' => $account->id,
                'balance' => $account->getBalance()
            ]
        ], Response::HTTP_OK);
    }

    private function eventTransfer(Request $request)
    {
        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $amount = $request->input('amount');

        // Tests
        if(empty($origin))
            return response()->json(["message" => "origin cannot be empty."], Response::HTTP_NOT_FOUND);

        if(!is_numeric($origin))
            return response()->json(["message" => "origin must be a integer."], Response::HTTP_NOT_FOUND);

        if(empty($destination))
            return response()->json(["message" => "destination cannot be empty."], Response::HTTP_NOT_FOUND);

        if(!is_numeric($destination))
            return response()->json(["message" => "destination must be a integer."], Response::HTTP_NOT_FOUND);

        if(empty($amount))
            return response()->json(["message" => "amount cannot be empty."], Response::HTTP_NOT_FOUND);

        if($amount <= 0)
            return response()->json(["message" => "amount must be greater then 0."], Response::HTTP_NOT_FOUND);

        // Logic
        try 
        {
            $originAccount = Account::find($origin);
            if(!$originAccount)
                return response()->json(["message" => "Origin account not found."], Response::HTTP_NOT_FOUND);

            $destinationAccount = Account::find($destination);
            if(!$destinationAccount)
                return response()->json(["message" => "Destination account not found."], Response::HTTP_NOT_FOUND);

            if($originAccount->getBalance() < $amount)
                throw new Exception("Origin account has insufficient funds");

            $originAccount->withdraw($amount);
            $originAccount->save();

            $destinationAccount->deposit($amount);
            $destinationAccount->save();
        } 
        catch(\Exception $e)
        {
            return response()->json(["message" => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
        
        // Response
        return response()->json([
            'origin' => [
                'id' => $originAccount->id,
                'balance' => $originAccount->getBalance()
            ],
            'destination' => [
                'id' => $destinationAccount->id,
                'balance' => $destinationAccount->getBalance()
            ]
        ], Response::HTTP_OK);
    }

    public function event(Request $request)
    {   
        $type = $request->input('type');

        if(empty($type))
            return response()->json(["message" => 'type cannot be empty.'], Response::HTTP_NOT_FOUND);

        switch ($type) 
        {
            case self::EVENT_DEPOSIT: return $this->eventDeposit($request); break;
            case self::EVENT_WITHDRAW: return $this->eventWithdraw($request); break;
            case self::EVENT_TRANSFER: return $this->eventTransfer($request); break;
            
            default: 
                return response()->json(["message" => 'type not found.'], Response::HTTP_NOT_FOUND);
                break;
        }
    }
}
