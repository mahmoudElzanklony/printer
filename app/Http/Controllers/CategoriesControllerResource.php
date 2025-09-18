<?php

namespace App\Http\Controllers;

use App\Actions\CheckForUploadImage;
use App\Actions\VerifyAccess;
use App\Http\Requests\categoriesFormRequest;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\PropertyHeadingResource;
use App\Http\Traits\upload_image;
use App\Models\categories;
use App\Models\properties;
use App\Models\properties_heading;
use App\Services\FormRequestHandleInputs;
use App\Services\Messages;
use Illuminate\Support\Facades\DB;

class CategoriesControllerResource extends Controller
{
    use upload_image;

    /**
     * Display a listing of the resource.
     */
    public function __construct()
    {

        $this->middleware('auth:sanctum')->except('index', 'show');
        $this->middleware('optional_auth')->only('index', 'show');

    }

    public function index()
    {
        VerifyAccess::execute('pi pi-sitemap|/categories|read');
        $data = categories::query()->orderBy('id', 'DESC')->get();
        return CategoryResource::collection($data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function save($data, $image)
    {
        DB::beginTransaction();
        // prepare data to be created or updated
        $data['user_id'] = auth()->id();
        $data = FormRequestHandleInputs::handle_inputs_langs($data, ['name', 'info']);
        // start save category data
        $category = categories::query()->updateOrCreate([
            'id' => $data['id'] ?? null
        ], $data);
        // check if there is any image related to this category and save it
        if (!(array_key_exists('id', $data)) || (array_key_exists('id', $data) && $image != null)) {
            $this->check_upload_image($image, 'categories', $category->id, 'categories');
        }
        // save properties
        // Fetch the prices for the properties
        $properties = properties::whereIn('id', $data['properties'])->get();

        foreach ($properties as $property) {
            $propertiesWithPrices[$property->id] = ['price' => $property->price];
        }
        $category->properties()->sync($propertiesWithPrices);
        // Load the category with the associated image
        $category->load('image');

        DB::commit();
        // return response
        return Messages::success(__('messages.saved_successfully'), CategoryResource::make($category));
    }

    public function store(categoriesFormRequest $request)
    {
        VerifyAccess::execute('pi pi-sitemap|/categories|create');
        return $this->save($request->validated(), request()->file('image'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $cat = categories::query()->where('id', $id)->FailIfNotFound(__('errors.not_found_data'));


        // i want to groupBy heading
        $data = categories::query()->with('properties')->find($id);
        ///////////////////////////////////////////// bad way
        $headings_ids = $data->properties->map(function ($e) {
            return $e->property_id_heading;
        })->unique();
        $properties_ids = $data->properties->map(function ($e) {
            return $e->id;
        })->unique();
        $bad_way = properties_heading::query()->whereIn('id', $headings_ids)->with('properties', function ($e) use ($properties_ids) {
            $e->whereIn('id', $properties_ids);
        })->get();
        return Messages::success('', ['category-info' => PropertyHeadingResource::collection($bad_way), 'category' => $cat]);
        ///////////////////////////////////////////// bad way
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(categoriesFormRequest $request, $id)
    {
        VerifyAccess::execute('pi pi-sitemap|/categories|update');
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
