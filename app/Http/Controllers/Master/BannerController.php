<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BannerController extends Controller
{
    // list
    public function index()
    {
        $banners = Banner::all();
        return response()->json([
            'status' => 'success',
            'data' => $banners,
            'message' => 'List banner'
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        // list all
        $search = $request->search;
        $status = $request->status;
        $banner =  Banner::query();
        if ($search) {
            $banner->where(function ($query) use ($search) {
                $query->where('banner_title', 'like', "%$search%");
                $query->orWhere('banner_url', 'like', "%$search%");
                $query->orWhere('banner_description', 'like', "%$search%");
            });
        }

        if ($status) {
            $banner->where('status', $status);
        }

        $banners = $banner->orderBy('created_at', 'desc')->paginate($request->perpage);
        return response()->json([
            'status' => 'success',
            'data' => $banners,
            'message' => 'List banner'
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
            $data = [
                'banner_title' => $request->banner_title,
                'banner_url' => $request->banner_url,
                'banner_description' => $request->banner_description,
                'status' => $request->status ?? 1,
            ];

            // save image
            if ($request->hasFile('banner_image')) {
                $file = $request->file('banner_image');
                $filename = $file->getClientOriginalName();
                $file->move(public_path('images/banner'), $filename);
                $data['banner_image'] = $filename;
            }

            $banner = Banner::create($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $banner,
                'message' => 'Banner created successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Banner created failed',

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
        $banner = Banner::find($id);

        if (!$banner) {
            return response()->json([
                'status' => 'error',
                'message' => 'Banner not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $banner,
            'message' => 'Banner detail'
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
            $banner = Banner::find($id);

            if (!$banner) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Banner not found'
                ], 404);
            }

            $data = [
                'banner_title' => $request->banner_title,
                'banner_url' => $request->banner_url,
                'banner_description' => $request->banner_description,
                'status' => $request->status,
            ];

            // save image
            if ($request->hasFile('banner_image')) {
                $file = $request->file('banner_image');
                $filename = $file->getClientOriginalName();
                $file->move(public_path('images/banner'), $filename);
                $data['banner_image'] = $filename;
            }

            $banner->update($data);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $banner,
                'message' => 'Banner updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Banner updated failed',

            ], 400);
        }
    }

    // update status of banner
    public function updateStatus(Request $request, $banner_id)
    {
        try {
            DB::beginTransaction();
            $banner = Banner::find($banner_id);

            if (!$banner) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Banner not found'
                ], 404);
            }

            $banner->update([
                'status' => $request->status
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $banner,
                'message' => 'Banner updated successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Banner updated failed',

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
            $banner = Banner::find($id);

            if (!$banner) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Banner not found'
                ], 404);
            }

            $banner->delete();

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Banner deleted successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Banner deleted failed'
            ], 400);
        }
    }
}
