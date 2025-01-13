<?php

namespace App\Http\Controllers;

use App\Actions\OrdersWithAllDataAction;
use App\Filters\EndDateFilter;
use App\Filters\orders\RateOrderFilter;
use App\Filters\orders\StatusOrderFilter;
use App\Filters\StartDateFilter;
use App\Http\Requests\rateFormRequest;
use App\Http\Resources\OrderRateResource;
use App\Http\Resources\OrderResource;
use App\Models\orders_rates;
use App\Policies\TestPolicy;
use App\Services\Messages;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RatesController extends Controller
{
    //
    public function index()
    {
        $data = orders_rates::query()->with('order.user')->when(auth()->user()->hasRole('client'),
            function ($query) {$query->whereHas('order.user',fn($q) => $q->where('user_id',auth()->id()));
        });
        $output  = app(Pipeline::class)
            ->send($data)
            ->through([
                StartDateFilter::class,
                EndDateFilter::class,
            ])
            ->thenReturn()
            ->paginate(request('limit') ?? 10);
        return OrderRateResource::collection($output);

        //$this->authorize('index', TestPolicy::class);
        //return auth()->user()->can('facility-facility-settings');

    }

    public function create(rateFormRequest $request)
    {
        $data = $request->validated();
        // save order rate
        orders_rates::query()->updateOrCreate([
            'order_id'=>$data['order_id']
        ],$data);
        return Messages::success(__('messages.operation_done_successfully'),OrderResource::make(OrdersWithAllDataAction::get()->find($data['order_id'])));
        // return auth()->user()->can('facility-facility-settings');

       // $this->authorize('index', TestPolicy::class);
    }
}
