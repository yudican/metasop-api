<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\LevelPrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LevelPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $search = $request->search;
        $role_id = $request->role_id;
        $level =  LevelPrice::query();
        if ($search) {
            $level->where(function ($query) use ($search) {
                $query->where('level_name', 'like', "%$search%");
                $query->orWhereHas('roles', function ($query) use ($search) {
                    $query->where('role_name', 'like', "%$search%");
                });
            });
        }

        if ($role_id) {
            $level->where('role_id', $role_id);
        }

        $levels = $level->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $levels,
            'message' => 'List level'
        ]);
    }

    // list all
    public function index()
    {
        $levels = LevelPrice::all();
        return response()->json([
            'status' => 'success',
            'data' => $levels,
            'message' => 'List level'
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
        try {
            DB::beginTransaction();
            $level = LevelPrice::create([
                'level_name' => $request->level_name,
                'role_id' => $request->role_id,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $level,
                'message' => 'Level created successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Level created failed',
                'error' => $th->getMessage()
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
        $level = LevelPrice::find($id);

        return response()->json([
            'status' => 'success',
            'data' => $level,
            'message' => 'Detail level'
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
        try {
            DB::beginTransaction();
            $level = LevelPrice::find($id);
            $level->update([
                'level_name' => $request->level_name,
                'role_id' => $request->role_id,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $level,
                'message' => 'Level updated successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Level updated failed'
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
        try {
            DB::beginTransaction();
            $level = LevelPrice::find($id);
            $level->delete();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Level deleted successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Level deleted failed'
            ], 400);
        }
    }
}
