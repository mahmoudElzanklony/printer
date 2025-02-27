<?php

namespace App\Http\Controllers;

use App\Actions\AddToWalletHistoryAction;
use App\Http\Resources\WalletHistoryResource;
use App\Models\wallet_history;
use App\Services\Messages;

class WalletHistoryController extends Controller
{
    //
    public function index()
    {
        $data = wallet_history::active()->orderBy('id', 'DESC')->get();

        return WalletHistoryResource::collection($data);
    }

    public function charge()
    {
        $money = request('money');
        $user = auth()->user();
        // add wallet to user
        $user->update([
            'wallet' => $user->wallet + $money,
        ]);
        $result = AddToWalletHistoryAction::save($money, 'plus', 'charge', auth()->id());

        return Messages::success(__('messages.saved_successfully'), WalletHistoryResource::make($result));
    }
}
