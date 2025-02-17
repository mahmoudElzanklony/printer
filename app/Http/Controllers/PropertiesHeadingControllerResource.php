<?php

namespace App\Http\Controllers;

use App\Actions\VerifyAccess;
use App\Http\Requests\propertiesHeadingFormRequest;
use App\Http\Resources\PropertyHeadingResource;
use App\Http\Traits\upload_image;
use App\Models\properties_heading;
use App\Services\FormRequestHandleInputs;
use App\Services\Messages;
use Illuminate\Support\Facades\DB;

class PropertiesHeadingControllerResource extends Controller
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
        VerifyAccess::execute('pi pi-palette|/properties-headings|read');
        $data = properties_heading::query()
            ->with('properties', function ($query) {
                $query->with('icon_info')->with('image');
            })
            ->with('image')->orderBy('id', 'DESC')->get();

        return PropertyHeadingResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function save($data, $image = null)
    {
        DB::beginTransaction();
        // prepare data to be created or updated
        $data['user_id'] = auth()->id();
        $data = FormRequestHandleInputs::handle_inputs_langs($data, ['name']);
        // start save category data
        $property_heading = properties_heading::query()->updateOrCreate([
            'id' => $data['id'] ?? null,
        ], $data);
        // check if there is any image related to this category and save it
        if (! (array_key_exists('id', $data)) || (array_key_exists('id', $data) && $image != null)) {
            $this->check_upload_image($image, 'categories', $property_heading->id, 'properties_heading');
        }

        DB::commit();

        // return response
        return Messages::success(__('messages.saved_successfully'), PropertyHeadingResource::make($property_heading));
    }

    public function store(propertiesHeadingFormRequest $request)
    {
        //
        VerifyAccess::execute('pi pi-palette|/properties-headings|create');
        $data = $request->validated();

        return $this->save($data, request()->file('image') ?? null);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $data = properties_heading::query()
            ->with('properties', function ($query) {
                $query->with('icon_info')->with('image');
            })
            ->with('image')
            ->where('id', $id)->FailIfNotFound(__('errors.not_found_data'));

        return PropertyHeadingResource::make($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(propertiesHeadingFormRequest $request, string $id)
    {
        VerifyAccess::execute('pi pi-palette|/properties-headings|update');
        properties_heading::query()->where('id', $id)->FailIfNotFound(__('errors.not_found_data'));
        $data = $request->validated();
        $data['id'] = $id;

        return $this->save($data, request()->file('image') ?? null);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
