<?php

namespace App\Http\Controllers;

use App\Actions\CheckForUploadImage;
use App\Filters\CountryIdFilter;
use App\Filters\EndDateFilter;
use App\Filters\StartDateFilter;
use App\Filters\users\UserNameFilter;
use App\Filters\users\WalletFilter;
use App\Http\Requests\categoriesFormRequest;
use App\Http\Requests\citiesFormRequest;
use App\Http\Requests\countriesFormRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\PropertyHeadingResource;
use App\Models\categories;
use App\Models\categories_properties;
use App\Models\cities;
use App\Models\countries;
use App\Models\properties;
use App\Models\properties_heading;
use App\Services\FormRequestHandleInputs;
use App\Services\Messages;
use Illuminate\Http\Request;
use App\Http\Traits\upload_image;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class CitiesControllerResource extends Controller
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
        $data = cities::query()->with('user')
            ->orderBy('id','DESC');

        $output  = app(Pipeline::class)
            ->send($data)
            ->through([
                StartDateFilter::class,
                EndDateFilter::class,
                CountryIdFilter::class,
            ])
            ->thenReturn()
            ->get();


        return CityResource::collection($output);
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
        $obj = cities::query()->updateOrCreate([
            'id'=>$data['id'] ?? null
        ],$data);

        $obj->load(['user','country']);

        DB::commit();
        // return response
        return Messages::success(__('messages.saved_successfully'),CityResource::make($obj));
    }

    public function store(citiesFormRequest $request)
    {
        return $this->save($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $obj  = cities::query()->where('id',$id)
            ->with(['user','country'])
            ->firstOrFailWithCustomError(__('errors.not_found_data'));


        return CityResource::make($obj);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(citiesFormRequest $request , $id)
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
