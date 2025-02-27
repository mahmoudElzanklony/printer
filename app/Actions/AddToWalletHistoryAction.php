<?php

namespace App\Actions;

use App\Models\wallet_history;

class AddToWalletHistoryAction
{
    public static function save($amount, $status, $type, $user_id)
    {
        return wallet_history::query()->create([
            'user_id' => $user_id,
            'amount' => $amount,
            'status' => $status,
            'type' => $type,
        ]);
    }
}
