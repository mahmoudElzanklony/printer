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
use App\Http\patterns\ChainResponsabilites\savedLocation\CreateAreaRes;
use App\Http\patterns\ChainResponsabilites\savedLocation\CreateCityRes;
use App\Http\Requests\categoriesFormRequest;
use App\Http\Requests\citiesFormRequest;
use App\Http\Requests\countriesFormRequest;
use App\Http\Requests\savedLocationFormRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\CityResource;
use App\Http\Resources\CountryResource;
use App\Http\Resources\PropertyHeadingResource;
use App\Http\Resources\SavedLocationResource;
use App\Models\categories;
use App\Models\categories_properties;
use App\Models\cities;
use App\Models\countries;
use App\Models\properties;
use App\Models\properties_heading;
use App\Models\saved_locations;
use App\Models\User;
use App\Services\CheckMaxBeforeSaveService;
use App\Services\FormRequestHandleInputs;
use App\Services\Messages;
use Illuminate\Http\Request;
use App\Http\Traits\upload_image;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;

class SavedLocationsControllerResource extends Controller
{
    use upload_image;
    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('optional_auth')->only('index','show');

    }
    public function index()
    {
        $data = saved_locations::query()->with(['area'])
            ->when(auth()->user()->roleName() == 'client',function($query){
                $query->where('user_id',auth()->id());
            })
            ->orderBy('is_default','DESC');

        $output  = app(Pipeline::class)
            ->send($data)
            ->through([
                IsDefaultFilter::class,
                LimitFilter::class
            ])
            ->thenReturn()
            ->get();


        return SavedLocationResource::collection($output);
    }

    public function create_new_one($data)
    {
        if(isset($data['city_id'])) {
            if (!(is_numeric($data['city_id'])) || !(is_numeric($data['area_id']))) {
                // create city or area

                $city = new CreateCityRes();
                $area = new CreateAreaRes();
                $city->setNext($area);
                $city->handle($data);
                $data = $area->get_last_data();
            }
        }
        return $data;


    }

    /**
     * Store a newly created resource in storage.
     */
    public function save($data)
    {


        DB::beginTransaction();
        $data = $this->create_new_one($data);

        // prepare data to be created or updated
        $data['user_id'] = auth()->id();
        if($data['is_default'] == 1){
            // change default one to non default
            ChangeDefaultLocationToNonAction::execute();
        }

        $obj = saved_locations::query()->updateOrCreate([
            'id'=>$data['id'] ?? null
        ],$data);

        $obj->load(['user','area']);

        DB::commit();
        // return response
        return Messages::success(__('messages.saved_successfully'),SavedLocationResource::make($obj));
    }

    public function store(savedLocationFormRequest $request)
    {
        CheckMaxBeforeSaveService::execute_saved_locations();
        return $this->save($request->validated());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $obj  = saved_locations::query()->where('id',$id)
            ->with(['user','area'])
            ->firstOrFailWithCustomError(__('errors.not_found_data'));


        return CityResource::make($obj);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(savedLocationFormRequest $request , $id)
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
