<?php

namespace App\Http\Controllers;

use App\Actions\CheckForUploadImage;


use App\Filters\EndDateFilter;
use App\Filters\IsDefaultFilter;
use App\Filters\LimitFilter;
use App\Filters\StartDateFilter;
use App\Http\patterns\builder\HistorySmsDynamicBuilder;
use App\Http\Requests\adFormRequest;

use App\Http\Requests\smsHistoryFormRequest;
use App\Http\Resources\AdResource;


use App\Http\Resources\SmsHistoryResource;
use App\Models\ads;
use App\Models\saved_locations;
use App\Models\sms_history;
use App\Services\FormRequestHandleInputs;
use App\Services\Messages;
use Illuminate\Http\Request;
use App\Http\Traits\upload_image;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class SmsHistoryControllerResource extends Controller
{
    use upload_image;
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }
    public function index()
    {
        $data = sms_history::query()->with('user')
            ->orderBy('id','DESC');

        $output  = app(Pipeline::class)
            ->send($data)
            ->through([
                StartDateFilter::class,
                EndDateFilter::class,
                LimitFilter::class
            ])
            ->thenReturn()
            ->get();


        return SmsHistoryResource::collection($output);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function save($data)
    {
        DB::beginTransaction();

        $obj = new HistorySmsDynamicBuilder($data);
        $result = $obj->get_users_number()->save_DB();

        DB::commit();
        // return response
        return Messages::success(__('messages.saved_successfully'),
            SmsHistoryResource::make($result));
    }

    public function store(smsHistoryFormRequest $request)
    {
        return $this->save($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $data = sms_history::query()->with('user')
            ->where('id', $id)
            ->firstOrFailWithCustomError(__('errors.not_found_data'));

        return SmsHistoryResource::make($data);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(adFormRequest $request , $id)
    {


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
