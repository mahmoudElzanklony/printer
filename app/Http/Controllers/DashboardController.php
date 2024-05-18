<?php

namespace App\Http\Controllers;

use App\Actions\OrdersWithAllDataAction;
use App\Filters\EndDateFilter;
use App\Filters\orders\StatusOrderFilter;
use App\Filters\StartDateFilter;
use App\Http\Requests\controlWalletFormRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Messages;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;

class DashboardController extends Controller
{
    //
    public function users()
    {
        $data = User::query()->orderBy('id','DESC');
        $output  = app(Pipeline::class)
            ->send($data)
            ->through([
                StartDateFilter::class,
                EndDateFilter::class,
            ])
            ->thenReturn()
            ->paginate(10);
        return UserResource::collection($output);
    }

    public function add_money_to_wallet(controlWalletFormRequest $request)
    {
        $data = $request->validated();
        $user = User::query()->find($data['user_id']);
        // add wallet to user
        $user->update([
            'wallet'=>$user->wallet + $data['money']
        ]);
        return Messages::success(__('messages.operation_done_successfully'));
    }
}
