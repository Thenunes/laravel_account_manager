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
        return response('OK', Response::HTTP_OK);
    }

    public function balance(Request $request)
    {   
        $accountId = $request->input('account_id');
        if(empty($accountId)){
            // account_id cannot be empty.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        $account = Account::find($accountId);
        if(!$account){
            // Account not found.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        return response($account->getBalance(), Response::HTTP_OK);
    }

    private function eventDeposit(Request $request)
    {
        $destination = $request->input('destination');
        $amount = $request->input('amount');

        // Tests
        if(empty($destination))
        {
            // destination cannot be empty.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        if(!is_numeric($destination))
        {
            // destination must be a integer.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        if(empty($amount))
        {
            // amount cannot be empty.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        if($amount <= 0)
        {
            // amount must be greater then 0.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        // Logic
        try 
        {
            $account = Account::find($destination);
            if(!$account){
                // create if not exist
                $account = new Account($destination, 0);
            }
            
            $account->deposit($amount);
            $account->save();
        } 
        catch(\Exception $e)
        {
            return response(0, Response::HTTP_NOT_FOUND);
        }

        // Response
        return response('{"destination": {"id":"'.$account->id.'", "balance":'.$account->getBalance().'}}', Response::HTTP_CREATED);

        // return response()->json([
        //     'destination' => [
        //         'id' => $account->id,
        //         'balance' => $account->getBalance()
        //     ]
        // ], Response::HTTP_CREATED);
    }

    private function eventWithdraw(Request $request)
    {
        $origin = $request->input('origin');
        $amount = $request->input('amount');

        // Tests
        if(empty($origin)){
            // origin cannot be empty.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        if(!is_numeric($origin)){
            // origin must be a integer.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        if(empty($amount)){
            // amount cannot be empty.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        if($amount <= 0){
            // amount must be greater then 0.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        // Logic
        try 
        {
            $account = Account::find($origin);
            if(!$account){
                // Account not found.
                return response(0, Response::HTTP_NOT_FOUND);
            }

            $account->withdraw($amount);
            $account->save();
        } 
        catch(\Exception $e)
        {
            return response(0, Response::HTTP_NOT_FOUND);
        }
        
        // Response
        return response('{"origin": {"id":"'.$account->id.'", "balance":'.$account->getBalance().'}}', Response::HTTP_CREATED);

        // return response()->json([
        //     'origin' => [
        //         'id' => $account->id,
        //         'balance' => $account->getBalance()
        //     ]
        // ], Response::HTTP_CREATED);
    }

    private function eventTransfer(Request $request)
    {
        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $amount = $request->input('amount');

        // Tests
        if(empty($origin))
        {
            // origin cannot be empty.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        if(!is_numeric($origin))
        {
            // origin must be a integer.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        if(empty($destination))
        {
            // destination cannot be empty.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        if(!is_numeric($destination))
        {
            // destination must be a integer.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        if(empty($amount))
        {
            // amount cannot be empty.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        if($amount <= 0)
        {
            // amount must be greater then 0.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        // Logic
        try 
        {
            $originAccount = Account::find($origin);
            if(!$originAccount){
                // Origin account not found.
                return response(0, Response::HTTP_NOT_FOUND);
            }

            $destinationAccount = Account::find($destination);
            if(!$destinationAccount){
                // create if not exist
                $destinationAccount = new Account($destination, 0);
            }

            if($originAccount->getBalance() < $amount){
                // Origin account has insufficient funds.
                return response(0, Response::HTTP_NOT_FOUND);
            }

            $originAccount->withdraw($amount);
            $originAccount->save();

            $destinationAccount->deposit($amount);
            $destinationAccount->save();
        } 
        catch(\Exception $e)
        {
            return response(0, Response::HTTP_NOT_FOUND);
        }
        
        // Response
        return response('{"origin": {"id":"'.$originAccount->id.'", "balance":'.$originAccount->getBalance().'}, "destination": {"id":"'.$destinationAccount->id.'", "balance":'.$destinationAccount->getBalance().'}}', Response::HTTP_CREATED);

        // return response()->json([
        //     'origin' => [
        //         'id' => $originAccount->id,
        //         'balance' => $originAccount->getBalance()
        //     ],
        //     'destination' => [
        //         'id' => $destinationAccount->id,
        //         'balance' => $destinationAccount->getBalance()
        //     ]
        // ], Response::HTTP_CREATED);
    }

    public function event(Request $request)
    {   
        $type = $request->input('type');

        if(empty($type))
        {
            // type cannot be empty.
            return response(0, Response::HTTP_NOT_FOUND);
        }

        switch ($type) 
        {
            case self::EVENT_DEPOSIT: return $this->eventDeposit($request); break;
            case self::EVENT_WITHDRAW: return $this->eventWithdraw($request); break;
            case self::EVENT_TRANSFER: return $this->eventTransfer($request); break;
            
            default: 
                // type not found.
                return response(0, Response::HTTP_NOT_FOUND);
                break;
        }
    }
}
