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

            $menus?->map(function ($menu) {
                if (!in_array($menu['menu_route'], ['#', null, ''])) {
                    $menu['spa_route'] = null;
                    try {
                        $route = route($menu['menu_route']);
                        $menu['menu_url'] = $route;
                        if (strpos($menu['menu_route'], 'spa') !== false) {
                            $menu['spa_route'] = str_replace(env('APP_URL'), '', $route);
                        }
                    } catch (\Throwable $th) {
                        $menu['menu_url'] = '#';
                        $menu['spa_route'] = null;
                    }
                    // if ($menu['badge']) {
                    //     $menu['badge_count'] = getBadge($menu['badge']);
                    // }
                }
                foreach ($menu['children'] as $key => $children) {
                    $menu['children'][$key]['menu_url'] = '#';
                    $menu['children'][$key]['spa_route'] = null;
                    if ($children['menu_route']) {
                        try {
                            $route = route($children['menu_route']);
                            $menu['children'][$key]['menu_url'] = $route;
                            if (strpos($children['menu_route'], 'spa') !== false) {
                                $menu['children'][$key]['spa_route'] = str_replace(env('APP_URL'), '', $route);
                            }
                        } catch (\Throwable $th) {
                            $menu['children'][$key]['menu_url'] = '#';
                            $menu['children'][$key]['spa_route'] = false;
                        }
                        // if ($children['badge']) {
                        //     $menu['children'][$key]['badge_count'] = getBadge($children['badge']);
                        // }
                    }
                }
            });
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
