<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Http\Resources\Setting\MenuResource;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // load all menu
        $menus = Menu::with('roles')->get();
        return response()->json([
            'message' => 'Menu Data',
            'data' => MenuResource::collection($menus),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // store menu
        try {
            DB::beginTransaction();

            $menu = Menu::create([
                'menu_label' => $request->menu_label,
                'menu_icon' => $request->menu_icon,
                'menu_route' => $request->menu_route,
                'menu_order' => $request->menu_order,
                'show_menu' => $request->show_menu,
                'parent_id' => $request->parent_id,
            ]);

            // attach role
            $menu->roles()->attach($request->role_id);

            DB::commit();

            return response()->json([
                'message' => 'Create Menu Success',
                'data' => new MenuResource($menu),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Create Menu Failed',
                'data' => $th->getMessage(),
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $menu = Menu::with('roles')->find($id);
        return response()->json([
            'message' => 'Menu Data',
            'data' => new MenuResource($menu),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // update menu
        try {
            DB::beginTransaction();

            $menu = Menu::find($id);
            $menu->update([
                'menu_label' => $request->menu_label,
                'menu_icon' => $request->menu_icon,
                'menu_route' => $request->menu_route,
                'menu_order' => $request->menu_order,
                'show_menu' => $request->show_menu,
                'parent_id' => $request->parent_id,
            ]);

            // attach role
            $menu->roles()->sync($request->role_id);

            DB::commit();

            return response()->json([
                'message' => 'Update Menu Success',
                'data' => new MenuResource($menu),
                'request' => $request->all()
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Update Menu Failed',
                'data' => $th->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // delete menu
        try {
            DB::beginTransaction();

            $menu = Menu::find($id);
            $menu->roles()->detach();
            $menu->delete();

            DB::commit();

            return response()->json([
                'message' => 'Delete Menu Success',
                'data' => new MenuResource($menu),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'message' => 'Delete Menu Failed',
                'data' => $th->getMessage(),
            ], 400);
        }
    }
}
