<?php

namespace App\Http\Controllers\Employees;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        //
        $users = User::query()->select(['id', 'username'])->pluck('id', 'username');
        //$users = User::query()->select(['id'])->pluck('id');
        return $users;
        return 'test one';
    }
}
