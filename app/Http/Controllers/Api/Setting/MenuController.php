<?php

namespace App\Http\Controllers\Api\Setting;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Setting\Menu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    //
    public function index()
    {
        $req = [
            'order_by' => request('order_by') ?? 'created_at',
            'sort' => request('sort') ?? 'asc',
            'page' => request('page') ?? 1,
            'per_page' => request('per_page') ?? 10,
        ];
        $raw = Menu::query();
        $raw->when(request('q'), function ($q) {
            $q->where(function ($query) {
                $query->where('title', 'like', '%' . request('q') . '%')
                    ->orWhere('name', 'like', '%' . request('q') . '%');
            });
        })
            ->with('children')
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);


        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    public function store(Request $request)
    {
        $id = $request->id;
        if ($request->id && !Menu::find($request->id)) {
            return new JsonResponse(['message' => 'Menu tidak ditemukan'], 410);
        }
        $validated = $request->validate([
            'title' => 'required',
            'icon' => 'nullable',
            'url' => 'nullable',
            'name' => 'nullable',
            'view' => 'nullable',
            'component' => 'nullable',
        ], [
            'title.required' => 'Title Menu wajib diisi.'
        ]);



        $menu = Menu::updateOrCreate(
            [
                'id' =>  $id
            ],
            $validated
        );
        return new JsonResponse([
            'data' => $menu,
            'message' => 'Data menu berhasil disimpan'
        ]);
    }

    public function hapus(Request $request)
    {
        $menu = Menu::find($request->id);
        if (!$menu) {
            return new JsonResponse([
                'message' => 'Data menu tidak ditemukan'
            ], 410);
        }
        $menu->children()->delete();
        $menu->delete();
        return new JsonResponse([
            'data' => $menu,
            'message' => 'Data menu berhasil dihapus'
        ]);
    }
}
