<?php

namespace App\Http\Controllers\Api\Setting;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Setting\HakAkses;
use App\Models\Setting\Menu;
use App\Models\Setting\Submenu;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HakAksesController extends Controller
{
    //
    public function index(Request $request)
    {

        $data = User::with([
            'akses'
        ])->find($request->id);

        $menuIds = collect($data['akses'])->pluck('menu_id')->unique()->values();
        $subMenuIds = collect($data['akses'])->pluck('submenu_id')->unique()->values();
        $menus = Menu::wherein('id', $menuIds)->get();
        $submenus = Submenu::wherein('id', $subMenuIds)->get();
        $result = [];
        foreach ($menus as $key) {

            $key['children'] = $submenus->where('menu_id', $key->id)->values();
            $result[] = $key;
        }
        $data->items = $result;

        return new JsonResponse([
            'data' => $data,
            // 'result' => $result,
            // 'menuIds' => $menuIds,
            // 'subMenuIds' => $subMenuIds,
        ]);
    }
    public function grant(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'menus' => 'required|array|min:1',
            'menus.*' => 'required|array:menu_id,submenu_id',
            'menus.*.menu_id' => 'required|integer|exists:menus,id',
            'menus.*.submenu_id' => 'nullable|integer|exists:submenus,id'
        ], [
            'user_id.required' => 'User ID wajib diisi.',
            'menus.required' => 'Field menus wajib dikirim.',
            'menus.array' => 'Field menus harus berupa array.',
            'menus.min' => 'Minimal harus ada satu item menu.',

            'menus.*.required' => 'Setiap item wajib dikirim dan harus berisi menu_id dan submenu_id.',
            'menus.*.array' => 'Setiap item harus berupa array dengan menu_id dan submenu_id.',

            'menus.*.menu_id.required' => 'Menu ID wajib diisi di setiap item.',
            'menus.*.menu_id.integer' => 'Menu ID harus berupa angka.',
            'menus.*.menu_id.exists' => 'Menu ID tidak ditemukan di database.',

            'menus.*.submenu_id.integer' => 'Submenu ID harus berupa angka.',
            'menus.*.submenu_id.exists' => 'Submenu ID tidak ditemukan di database.'
        ]);

        try {
            DB::beginTransaction();
            foreach ($validated['menus'] as $key) {
                $ada = HakAkses::where('user_id', $request->user_id)->where('menu_id', $key['menu_id'])->where('submenu_id', $key['submenu_id'])->first();
                if (!$ada) {
                    HakAkses::create([
                        'user_id' => $request->user_id,
                        'menu_id' => $key['menu_id'],
                        'submenu_id' => $key['submenu_id']
                    ]);
                }
            }
            $data = User::with('akses')->find($request->user_id);
            $menuIds = collect($data['akses'])->pluck('menu_id')->unique()->values();
            $subMenuIds = collect($data['akses'])->pluck('submenu_id')->unique()->values();
            $menus = Menu::wherein('id', $menuIds)->get();
            $submenus = Submenu::wherein('id', $subMenuIds)->get();
            $result = [];
            foreach ($menus as $key) {

                $key['children'] = $submenus->where('menu_id', $key->id)->values();
                $result[] = $key;
            }
            $data->items = $result;
            DB::commit();
            return new JsonResponse([
                'message' => 'Data Hak akses berhasil di disimpan',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }
    public function revoke(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'menus' => 'required|array|min:1',
            'menus.*' => 'required|array:menu_id,submenu_id',
            'menus.*.menu_id' => 'required|integer|exists:menus,id',
            'menus.*.submenu_id' => 'nullable|integer|exists:submenus,id'
        ], [
            'user_id.required' => 'User ID wajib diisi.',
            'menus.required' => 'Field menus wajib dikirim.',
            'menus.array' => 'Field menus harus berupa array.',
            'menus.min' => 'Minimal harus ada satu item menu.',

            'menus.*.required' => 'Setiap item wajib dikirim dan harus berisi menu_id dan submenu_id.',
            'menus.*.array' => 'Setiap item harus berupa array dengan menu_id dan submenu_id.',

            'menus.*.menu_id.required' => 'Menu ID wajib diisi di setiap item.',
            'menus.*.menu_id.integer' => 'Menu ID harus berupa angka.',
            'menus.*.menu_id.exists' => 'Menu ID tidak ditemukan di database.',

            'menus.*.submenu_id.integer' => 'Submenu ID harus berupa angka.',
            'menus.*.submenu_id.exists' => 'Submenu ID tidak ditemukan di database.'
        ]);

        try {
            DB::beginTransaction();
            foreach ($validated['menus'] as $key) {
                $ada = HakAkses::where('user_id', $request->user_id)->where('menu_id', $key['menu_id'])->where('submenu_id', $key['submenu_id'])->first();
                if ($ada) $ada->delete();
            }
            $data = User::with('akses')->find($request->user_id);
            $menuIds = collect($data['akses'])->pluck('menu_id')->unique()->values();
            $subMenuIds = collect($data['akses'])->pluck('submenu_id')->unique()->values();
            $menus = Menu::wherein('id', $menuIds)->get();
            $submenus = Submenu::wherein('id', $subMenuIds)->get();
            $result = [];
            foreach ($menus as $key) {

                $key['children'] = $submenus->where('menu_id', $key->id)->values();
                $result[] = $key;
            }
            $data->items = $result;
            DB::commit();
            return new JsonResponse([
                'message' => 'Data Hak akses berhasil di Hapus',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace(),

            ], 410);
        }
    }
}
