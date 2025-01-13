<?php

namespace App\Http\Controllers;

use App\Actions\CheckForUploadImage;
use App\Filters\CityIdFilter;
use App\Filters\CountryIdFilter;
use App\Filters\EndDateFilter;
use App\Filters\StartDateFilter;
use App\Http\Requests\categoriesFormRequest;
use App\Http\Requests\citiesFormRequest;
use App\Http\Requests\countriesFormRequest;
use App\Http\Requests\shipmentFormRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\PropertyHeadingResource;
use App\Http\Resources\ShipmentPriceResource;
use App\Models\categories;
use App\Models\categories_properties;
use App\Models\cities;
use App\Models\countries;
use App\Models\properties;
use App\Models\properties_heading;
use App\Models\shipment_prices;
use App\Services\FormRequestHandleInputs;
use App\Services\Messages;
use Illuminate\Http\Request;
use App\Http\Traits\upload_image;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class ShipmentPricesControllerResource extends Controller
{
    use upload_image;
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('index','show');
        $this->middleware('optional_auth')->only('index','show');

    }
    public function index()
    {
        $data = shipment_prices::query()->with(['user','city'])->orderBy('id','DESC');

        $output  = app(Pipeline::class)
            ->send($data)
            ->through([
                StartDateFilter::class,
                EndDateFilter::class,
                CityIdFilter::class,
            ])
            ->thenReturn()
            ->get();


        return ShipmentPriceResource::collection($output);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function save($data)
    {
        DB::beginTransaction();
        // prepare data to be created or updated
        $data['user_id'] = auth()->id();
        $data =  FormRequestHandleInputs::handle_inputs_langs($data,['area']);

        // start save category data
        $obj = shipment_prices::query()->updateOrCreate([
            'id'=>$data['id'] ?? null
        ],$data);

        $obj->load(['user','city']);

        DB::commit();
        // return response
        return Messages::success(__('messages.saved_successfully'),ShipmentPriceResource::make($obj));
    }

    public function store(shipmentFormRequest $request)
    {
        return $this->save($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $obj  = shipment_prices::query()->where('id',$id)
            ->with(['user','city'])
            ->firstOrFailWithCustomError(__('errors.not_found_data'));


        return shipment_prices::make($obj);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(shipmentFormRequest $request , $id)
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
