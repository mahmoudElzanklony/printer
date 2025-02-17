<?php

namespace App\Http\Controllers;

use App\Http\patterns\builder\SavedPropertySettingBuilder;
use App\Http\Requests\savedPropertiesFormRequest;
use App\Http\Resources\SavedPropertiesSettingResource;
use App\Http\Traits\upload_image;
use App\Models\saved_properties_settings;
use App\Services\CheckMaxBeforeSaveService;
use App\Services\Messages;
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
        $this->middleware('optional_auth')->only('index', 'show');

    }

    public function index()
    {
        $data = saved_properties_settings::query()
            ->where('user_id', auth()->id())
            ->with(['answers.property.heading'])
            ->get();

        return SavedPropertiesSettingResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function save($basic_info, $properties)
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
        CheckMaxBeforeSaveService::execute_saved_properties();

        return $this->save(request()->except('properties'), request('properties'));
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
    public function update(savedPropertiesFormRequest $request, $id)
    {
        request()->merge(['id' => $id]);

        return $this->save(request()->except('properties'), request('properties'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
