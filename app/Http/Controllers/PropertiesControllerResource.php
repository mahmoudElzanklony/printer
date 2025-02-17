<?php

namespace App\Http\Controllers;

use App\Actions\VerifyAccess;
use App\Http\Requests\propertiesDataFormRequest;
use App\Http\Resources\PropertyResource;
use App\Http\Traits\upload_image;
use App\Models\properties;
use App\Models\properties_icons;
use App\Services\FormRequestHandleInputs;
use App\Services\Messages;
use Illuminate\Support\Facades\DB;

class PropertiesControllerResource extends Controller
{
    use upload_image;

    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('index', 'show');
        $this->middleware('optional_auth')->only('index', 'show');

    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        VerifyAccess::execute('pi pi-wrench|/properties|read');
        $data = properties::query()->with(['heading', 'image', 'icon_info'])->orderBy('id', 'DESC')->get();

        return PropertyResource::collection($data);
    }

    public function save_icon($data, $property_data)
    {
        if (isset($data['icon_name']) && isset($data['icon_label'])) {
            properties_icons::query()->updateOrCreate([
                'property_id' => $property_data->id,
            ], [
                'label' => $data['icon_label'],
                'icon' => $data['icon_name'],
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function save($data, $image)
    {
        DB::beginTransaction();
        // prepare data to be created or updated
        $data['user_id'] = auth()->id();
        $data = FormRequestHandleInputs::handle_inputs_langs($data, ['name']);

        // start save category data
        $property_data = properties::query()->updateOrCreate([
            'id' => $data['id'] ?? null,
        ], $data);

        // check if there is any image related to this category and save it
        if ($image != null && ! (array_key_exists('id', $data)) || (array_key_exists('id', $data) && $image != null)) {
            $this->check_upload_image($image, 'properties', $property_data->id, 'properties');
        }

        // save icon
        $this->save_icon($data, $property_data);

        $property_data->load('heading');
        $property_data->load('icon_info');

        DB::commit();

        // return response
        return Messages::success(__('messages.saved_successfully'), PropertyResource::make($property_data));
    }

    public function store(propertiesDataFormRequest $request)
    {
        //
        VerifyAccess::execute('pi pi-wrench|/properties|create');
        $data = $request->validated();

        return $this->save($data, request()->file('image'));

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $data = properties::query()->with(['heading', 'icon_info', 'image'])
            ->where('id', $id)->FailIfNotFound(__('errors.not_found_data'));

        return PropertyResource::make($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(propertiesDataFormRequest $request, string $id)
    {
        //
        VerifyAccess::execute('pi pi-wrench|/properties|update');
        properties::query()->where('id', $id)->FailIfNotFound(__('errors.not_found_data'));
        $data = $request->validated();
        $data['id'] = $id;

        return $this->save($data, request()->file('image'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
