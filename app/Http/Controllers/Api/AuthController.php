<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Formating\FormatingHelper;
use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Setting\Menu;
use App\Models\Setting\Submenu;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
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

        $raw = User::query();
        $raw->when(request('q'), function ($q) {
            $q->where(function ($query) {
                $query->where('nama', 'like', '%' . request('q') . '%')
                    ->orWhere('username', 'like', '%' . request('q') . '%')
                    ->orWhere('email', 'like', '%' . request('q') . '%')
                    ->orWhere('kode', 'like', '%' . request('q') . '%');
            });
        })
            // ->with('akses.items.children')
            ->where('username', '!=', 'sa')
            ->orderBy($req['order_by'], $req['sort']);
        $totalCount = (clone $raw)->count();
        $data = $raw->simplePaginate(request('per_page'));



        $resp = ResponseHelper::responseGetSimplePaginate($data, $req, $totalCount);
        return new JsonResponse($resp);
    }
    public function register(Request $request)
    {
        //
        $validateData = $request->validate([
            'nama' => 'required|string|between:2,100',
            'username' => 'required|string|between:2,100|unique:users',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
            'password_confirmation' => 'required',
            'hp' => 'nullable',
            'alamat' => 'nullable',
            'kode_jabatan' => 'nullable',
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'nama.string' => 'Nama harus berupa teks.',
            'nama.between' => 'Nama harus antara :min sampai :max karakter.',

            'username.required' => 'Username wajib diisi.',
            'username.string' => 'Username harus berupa teks.',
            'username.between' => 'Username harus antara :min sampai :max karakter.',
            'username.unique' => 'Username sudah digunakan.',

            'email.required' => 'Email wajib diisi.',
            'email.string' => 'Email harus berupa teks.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal :max karakter.',
            'email.unique' => 'Email sudah digunakan.',

            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password harus berupa teks.',
            'password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'password.min' => 'Password minimal :min karakter.',

            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
        ]);



        $user = User::create(array_merge($validateData, ['password' => bcrypt($request->password), 'kode' => '']));

        if (!$user) {
            return new JsonResponse(['message' => 'registrasi gagal'], 401);
        }
        $kode = FormatingHelper::genKodeDinLength($user->id, 5, 'USR');
        $user->update([
            'kode' => $kode
        ]);
        return response()->json([
            'message' => 'User successfully registered',
            'user' => $user,
            // 'valid' => array_merge($validator->validated(), ['password' => bcrypt($request->password)])
        ], 201);
    }
    public function login(Request $request)
    {
        //
        // $loginUserData = $request->validate([
        //     'email' => 'required|string|email',
        //     'password' => 'required'
        // ]);
        // $user = User::where('email', $loginUserData['email'])->first();

        $loginUserData = $request->validate([
            'login' => 'required|string',  // bisa username atau email
            'password' => 'required'
        ]);

        $login = $loginUserData['login'];

        // Cek apakah input berupa email
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        // Cari user berdasarkan email atau username
        $user = User::where($fieldType, $login)->first();

        if (!$user || !Hash::check($loginUserData['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid Credentials'
            ], 401);
        }
        $token = $user->createToken($user->name . '-AuthToken')->plainTextToken;
        return new JsonResponse([
            'token' => $token,
        ]);
    }
    public function logout(Request $request)
    {
        //
        $user = Auth::user();
        $user->tokens()->delete();

        return new JsonResponse([
            'message' => 'Logout Successfully'
        ]);
    }
    public function profile(Request $request)
    {
        //
        $user = Auth::user();
        if ($user->username == 'sa') {
            $items = Menu::with('children')->get();
            $data = User::find($user->id);
            $data->items = $items;
        } else {
            $data = User::with('akses')->find($user->id);
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
        }

        return new JsonResponse([
            'user' => $data
        ]);
    }

    public function update(Request $request)
    {
        //
        $user = User::find($request->id);
        if (!$user) {
            return new JsonResponse([
                'message' => 'User tidak ditemukan'
            ], 404);
        }
        $user->update([
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'hp' => $request->hp,
            'alamat' => $request->alamat,
            'kode_jabatan' => $request->kode_jabatan,
        ]);
        return new JsonResponse([
            'user' => $user
        ]);
    }
}
