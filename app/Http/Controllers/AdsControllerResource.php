<?php

namespace App\Http\Controllers;

use App\Actions\ChangeDefaultLocationToNonAction;
use App\Actions\CheckForUploadImage;
use App\Filters\EndDateFilter;
use App\Filters\IsDefaultFilter;
use App\Filters\LimitFilter;
use App\Filters\StartDateFilter;
use App\Filters\users\UserNameFilter;
use App\Filters\users\WalletFilter;
use App\Http\patterns\builder\SavedPropertySettingBuilder;

use App\Http\Requests\adFormRequest;
use App\Http\Requests\savedPropertiesFormRequest;

use App\Http\Resources\AdResource;
use App\Http\Resources\SavedPropertiesSettingResource;


use App\Models\ads;
use App\Services\FormRequestHandleInputs;
use App\Services\Messages;
use Illuminate\Http\Request;
use App\Http\Traits\upload_image;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class AdsControllerResource extends Controller
{
    use upload_image;
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->only('store','update');
    }
    public function index()
    {
        $data = ads::query()
            ->get();

        return AdResource::collection($data);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function save($data)
    {
        DB::beginTransaction();
        $data =  FormRequestHandleInputs::handle_inputs_langs($data,['name','info']);
        $result = ads::query()->updateOrCreate(['id' => $data['id'] ?? null], $data);

        DB::commit();
        // return response
        return Messages::success(__('messages.saved_successfully'),
            AdResource::make($result));
    }

    public function store(adFormRequest $request)
    {
        return $this->save($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $data = ads::query()
            ->where('id', $id)
            ->firstOrFailWithCustomError(__('errors.not_found_data'));

        return AdResource::make($data);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(adFormRequest $request , $id)
    {
        $data = $request->validated();
        $data['id'] = $id;
        return $this->save($data);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
