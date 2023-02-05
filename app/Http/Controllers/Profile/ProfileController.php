<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Auth\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    // get profile api
    public function getProfile(Request $request)
    {
        $user = Auth::user();
        return response()->json([
            'message' => 'success',
            'user' => new UserResource($user),
        ]);
    }

    public function getMenuList()
    {
        $role = auth()->user()->role;
        if ($role) {
            $role_id = $role->id;
            $menus = $role->menus()->where('show_menu', 1)->with('children')->whereHas('roles', function ($query) use ($role_id) {
                return $query->where('role_id', $role_id);
            })->where('parent_id')->orderBy('menu_order', 'ASC')->get();

            return response()->json([
                'message' => 'Menu Data',
                'data' => $menus,
            ]);
        }

        return response()->json([
            'message' => 'Menu Data',
            'data' => [],
        ]);
    }
}
