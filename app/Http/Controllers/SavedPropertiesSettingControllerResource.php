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
use App\Http\patterns\ChainResponsabilites\savedLocation\CreateAreaRes;
use App\Http\patterns\ChainResponsabilites\savedLocation\CreateCityRes;
use App\Http\Requests\categoriesFormRequest;
use App\Http\Requests\citiesFormRequest;
use App\Http\Requests\countriesFormRequest;
use App\Http\Requests\savedLocationFormRequest;
use App\Http\Requests\savedPropertiesFormRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\PropertyHeadingResource;
use App\Http\Resources\SavedLocationResource;
use App\Http\Resources\SavedPropertiesSettingResource;
use App\Models\categories;
use App\Models\categories_properties;
use App\Models\cities;
use App\Models\countries;
use App\Models\properties;
use App\Models\properties_heading;
use App\Models\saved_locations;
use App\Models\saved_properties_settings;
use App\Models\User;
use App\Services\FormRequestHandleInputs;
use App\Services\Messages;
use Illuminate\Http\Request;
use App\Http\Traits\upload_image;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class SavedPropertiesSettingControllerResource extends Controller
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
        $data = saved_properties_settings::query()
            ->where('user_id', auth()->id())
            ->with(['answers.property'])
            ->get();

        return SavedPropertiesSettingResource::collection($data);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function save($basic_info,$properties)
    {
        DB::beginTransaction();

        $obj = new SavedPropertySettingBuilder();
        $obj->build_main_setting($basic_info)->build_answers_setting($properties);

        $result = $obj->get_main_setting()->load(['answers.property']);

        DB::commit();
        // return response
        return Messages::success(__('messages.saved_successfully'),
            SavedPropertiesSettingResource::make($result));
    }

    public function store(savedPropertiesFormRequest $request)
    {
        return $this->save(request()->except('properties'),request('properties'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $data = saved_properties_settings::query()
            ->where('user_id', auth()->id())
            ->with(['answers.property'])
            ->where('id', $id)
            ->firstOrFailWithCustomError(__('errors.not_found_data'));

        return SavedPropertiesSettingResource::make($data);

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(savedPropertiesFormRequest $request , $id)
    {
        request()->merge(['id' => $id]);
        return $this->save(request()->except('properties'),request('properties'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
