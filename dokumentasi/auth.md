# 📄 Dokumentasi API AuthController

Base URL: `/api/v1/auth`

## 🔷 Get Users (List)

**GET** `/api/v1/auth/get-list`

> Mengambil daftar user dengan pencarian, sorting, dan pagination.

### Query Parameters

| Parameter | Type   | Required | Default      | Notes                                                                  |
| --------- | ------ | -------- | ------------ | ---------------------------------------------------------------------- |
| q         | string | ❌       | -            | Kata kunci untuk pencarian (`nama`, `username`, `email`, `kode`)       |
| order_by  | string | ❌       | `created_at` | Kolom untuk sorting, contoh: `nama`, `username`, `email`, `created_at` |
| sort      | string | ❌       | `asc`        | Arah sorting: `asc` atau `desc`                                        |
| per_page  | int    | ❌       | 15           | Jumlah item per halaman                                                |
| page      | int    | ❌       | 1            | Halaman yang diambil                                                   |

### Contoh Request

### Response (200)

````json
{
  "data": [
    {
      "id": 1,
      "nama": "Budi",
      "username": "budi123",
      "email": "budi@example.com",
      "kode": "USR00001",
      "hp": null,
      "alamat": null,
      "kode_jabatan": null,
      "created_at": "...",
      "updated_at": "..."
    },
    {
      "id": 2,
      "nama": "Andi",
      "username": "andi456",
      "email": "andi@example.com",
      "kode": "USR00002",
      "hp": null,
      "alamat": null,
      "kode_jabatan": null,
      "created_at": "...",
      "updated_at": "..."
    }
  ],
  "meta": {
    "current_page": 2,
    "from": 11,
    "last_page": 5,
    "path": "http://example.com/api/users",
    "per_page": 10,
    "to": 20,
    "total": 50
  }
}

---

## 🔷 Register

**POST** `/api/v1/auth/register`

> Digunakan untuk mendaftarkan user baru. dan hanya bisa digunakan oleh user yang sudah login

### Request Body

| Field                 | Type   | Required | Notes                             |
| --------------------- | ------ | -------- | --------------------------------- |
| nama                  | string | ✅       | Panjang 2–100 karakter            |
| username              | string | ✅       | Unik, panjang 2–100 karakter      |
| email                 | string | ✅       | Format email valid, unik, max 100 |
| password              | string | ✅       | Min. 6 karakter                   |
| password_confirmation | string | ✅       | Harus sama dengan `password`      |
| hp                    | string | ❌       | Nomor HP, opsional                |
| alamat                | string | ❌       | Opsional                          |
| kode_jabatan          | string | ❌       | Opsional                          |

### Contoh Request

```json
{
    "nama": "Budi",
    "username": "budi123",
    "email": "budi@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "hp": "08123456789",
    "alamat": "Jl. Contoh",
    "kode_jabatan": "STAFF"
}
````

### Response Sukses (201)

```json
{
    "message": "User successfully registered",
    "user": {
        "id": 1,
        "nama": "Budi",
        "username": "budi123",
        "email": "budi@example.com",
        "hp": "08123456789",
        "alamat": "Jl. Contoh",
        "kode_jabatan": "STAFF",
        "kode": "USR00001",
        "created_at": "...",
        "updated_at": "..."
    }
}
```

### Response Gagal (401)

```json
{
    "message": "registrasi gagal"
}
```

---

## 🔷 Login

**POST** `/api/v1/auth/login`

> Digunakan untuk login user. Bisa pakai `email` atau `username`.

### Request Body

| Field    | Type   | Required | Notes               |
| -------- | ------ | -------- | ------------------- |
| login    | string | ✅       | Email atau username |
| password | string | ✅       | Password            |

### Contoh Request

```json
{
    "login": "budi123",
    "password": "password123"
}
```

### Response Sukses (200)

```json
{
    "token": "token_sanctum_disini"
}
```

### Response Gagal (401)

```json
{
    "message": "Invalid Credentials"
}
```

---

## 🔷 Logout

**POST** `/api/v1/auth/logout`

> Logout user & hapus semua token.

### Header

Wajib mengirimkan token Bearer pada header:

```
Authorization: Bearer {token}
```

### Response (200)

```json
{
    "message": "Logout Successfully"
}
```

---

## 🔷 Profile

**GET** `/api/v1/auth/profile`

> Mengambil data user yang sedang login.

### Header

Wajib mengirimkan token Bearer pada header:

```
Authorization: Bearer {token}
```

### Response (200)

```json
{
    "user": {
        "id": 1,
        "nama": "Budi",
        "username": "budi123",
        "email": "budi@example.com",
        "hp": "08123456789",
        "alamat": "Jl. Contoh",
        "kode_jabatan": "STAFF",
        "kode": "USR00001",
        "created_at": "...",
        "updated_at": "..."
    }
}
```

---

### 🔷 Catatan:

✅ Semua response error validasi (422) akan otomatis dikembalikan Laravel dengan format:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field": ["Pesan error di sini."]
    }
}
```

✅ Gunakan token hasil login di semua request yang butuh autentikasi (`logout`, `profile`).

✅ Token menggunakan Laravel Sanctum.

---

### 📌 Update User

**POST** `/api/auth/update`

> Update data user berdasarkan ID.  
> Hanya bisa diakses oleh user yang sudah login (Sanctum).

#### Headers

| Header        | Value            |
| ------------- | ---------------- |
| Authorization | Bearer {token}   |
| Accept        | application/json |
| Content-Type  | application/json |

---

#### Body Parameters

| Parameter      | Type   | Required | Notes                        |
| -------------- | ------ | -------- | ---------------------------- |
| `id`           | int    | ✅ Yes   | ID user yang ingin diupdate. |
| `nama`         | string | ❌ No    | Nama lengkap user.           |
| `username`     | string | ❌ No    | Username user.               |
| `email`        | string | ❌ No    | Email user (harus unik).     |
| `hp`           | string | ❌ No    | Nomor HP.                    |
| `alamat`       | string | ❌ No    | Alamat lengkap.              |
| `kode_jabatan` | string | ❌ No    | Kode jabatan/role user.      |

---

#### 📄 Contoh Request

```http
POST /api/auth/update
Authorization: Bearer {token}
Accept: application/json
Content-Type: application/json

{
    "id": 1,
    "nama": "Andi Dermawan",
    "username": "andi123",
    "email": "andi@example.com",
    "hp": "081234567890",
    "alamat": "Jl. Merdeka No. 1",
    "kode_jabatan": "ADM"
}


```

## 📄 Contoh Response — Success 200 OK

```
{
"user": {
    "id": 1,
    "kode": "USR001",
    "nama": "Andi Dermawan",
    "username": "andi123",
    "email": "andi@example.com",
    "email_verified_at": null,
    "hp": "081234567890",
    "alamat": "Jl. Merdeka No. 1",
    "kode_jabatan": "ADM",
    "created_at": "2025-07-15T09:00:00.000000Z",
    "updated_at": "2025-07-15T10:00:00.000000Z"
    }
}
```

## 📄 Contoh Response — Error 404 Not Found

```
{
    "message": "User tidak ditemukan"
}

```
