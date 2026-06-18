<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class PermissionsController extends Controller
{
    public function index()
    {
        $matrix = config('permissions');

        return view('permissions.index', [
            'roles' => $matrix['roles'] ?? [],
            'permissionDescriptions' => $matrix['permission_descriptions'] ?? [],
        ]);
    }
}
