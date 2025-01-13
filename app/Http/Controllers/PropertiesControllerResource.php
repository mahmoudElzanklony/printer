<?php

namespace App\Http\Controllers;

use App\Actions\VerifyAccess;
use App\Http\Requests\propertiesDataFormRequest;
use App\Http\Resources\PropertyHeadingResource;
use App\Http\Resources\PropertyResource;
use App\Models\properties;
use App\Models\properties_heading;
use App\Services\FormRequestHandleInputs;
use App\Services\Messages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertiesControllerResource extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('index','show');
        $this->middleware('optional_auth')->only('index','show');

    }
    /**
     * Display a listing of the resource.
     */

    public function index()
    {
        //
        VerifyAccess::execute('pi-wrench|/properties|read');
        $data = properties::query()->with('heading')->orderBy('id','DESC')->get();
        return PropertyResource::collection($data);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function save($data)
    {
        DB::beginTransaction();
        // prepare data to be created or updated
        $data['user_id'] = auth()->id();
        $data =  FormRequestHandleInputs::handle_inputs_langs($data,['name']);
        // start save category data
        $property_data = properties::query()->updateOrCreate([
            'id'=>$data['id'] ?? null
        ],$data);


        $property_data->load('heading');

        DB::commit();
        // return response
        return Messages::success(__('messages.saved_successfully'),PropertyResource::make($property_data));
    }
    public function store(propertiesDataFormRequest $request)
    {
        //
        VerifyAccess::execute('pi-wrench|/properties|create');
        $data = $request->validated();
        return $this->save($data);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $data = properties::query()->with('heading')
            ->where('id', $id)->FailIfNotFound(__('errors.not_found_data'));
        return PropertyResource::make($data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(propertiesDataFormRequest $request, string $id)
    {
        //
        VerifyAccess::execute('pi-wrench|/properties|update');
        properties::query()->where('id', $id)->FailIfNotFound(__('errors.not_found_data'));
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
