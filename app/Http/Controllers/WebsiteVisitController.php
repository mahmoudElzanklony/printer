<?php

namespace App\Http\Controllers;

use App\Models\WebsiteVisit;
use App\Services\Messages;
use Illuminate\Http\Request;

class WebsiteVisitController extends Controller
{
    public function index()
    {
        return Messages::success(__('messages.operation_done_successfully'),[
            'total_visits'=>WebsiteVisit::query()->count(),
        ]);
    }
    public function store(Request $request)
    {
        $visit= WebsiteVisit::create([
            'user_id' => optional($request->user())->id,
            'user_agent' => $request->userAgent(),
        ]);
        return Messages::success(__('messages.operation_done_successfully'),$visit->toArray());
    }
}
