<?php

namespace App\Http\Controllers\Api\Setting;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Setting\Submenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubmenuController extends Controller
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
        $raw = Submenu::query();
        $raw->when(request('q'), function ($q) {
            $q->where(function ($query) {
                $query->where('title', 'like', '%' . request('q') . '%')
                    ->orWhere('name', 'like', '%' . request('q') . '%');
            });
        })
            ->with('menu')
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate($req['per_page']);


        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }

    public function store(Request $request)
    {
        $id = $request->id;
        $validated = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'title' => 'required',
            'icon' => 'nullable',
            'url' => 'nullable',
            'name' => 'nullable',
            'view' => 'nullable',
            'component' => 'nullable',
        ], [
            'title.required' => 'Title Submenu wajib diisi.',
            'menu_id.required' => 'id Menu wajib diisi.',
            'menu_id.exists' => 'Menu yang dipilih tidak valid.',
        ]);



        $submenu = Submenu::updateOrCreate(
            [
                'id' =>  $id
            ],
            $validated
        );
        return new JsonResponse([
            'data' => $submenu,
            'message' => 'Data submenu berhasil disimpan'
        ]);
    }

    public function hapus(Request $request)
    {
        $submenu = Submenu::find($request->id);
        if (!$submenu) {
            return new JsonResponse([
                'message' => 'Data submenu tidak ditemukan'
            ], 410);
        }
        $submenu->delete();
        return new JsonResponse([
            'data' => $submenu,
            'message' => 'Data submenu berhasil dihapus'
        ]);
    }
}
