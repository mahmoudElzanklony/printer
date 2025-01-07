<?php

namespace App\Http\Controllers;

use App\Actions\CheckForUploadImage;
use App\Http\Requests\categoriesFormRequest;
use App\Http\Requests\countriesFormRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\PropertyHeadingResource;
use App\Models\categories;
use App\Models\categories_properties;
use App\Models\countries;
use App\Models\properties;
use App\Models\properties_heading;
use App\Services\FormRequestHandleInputs;
use App\Services\Messages;
use Illuminate\Http\Request;
use App\Http\Traits\upload_image;
use Illuminate\Support\Facades\DB;

class CountriesControllerResource extends Controller
{
    use upload_image;
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('index','show');
    }
    public function index()
    {
        $data = countries::query()->with('user')->orderBy('id','DESC')->get();
        return CountryResource::collection($data);
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
        $obj = countries::query()->updateOrCreate([
            'id'=>$data['id'] ?? null
        ],$data);

        $obj->load('user');

        DB::commit();
        // return response
        return Messages::success(__('messages.saved_successfully'),CountryResource::make($obj));
    }

    public function store(countriesFormRequest $request)
    {
        return $this->save($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $obj  = countries::query()->where('id',$id)
            ->firstOrFailWithCustomError(__('errors.not_found_data'));


        return CountryResource::make($obj);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(countriesFormRequest $request , $id)
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
