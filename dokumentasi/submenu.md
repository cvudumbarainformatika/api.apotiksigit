# Submenu API Documentation

Base URL:

    /api/setting/submenu

Authentication:\

-   Menggunakan **Sanctum** middleware (`auth:sanctum`). - Semua endpoint
    **wajib** menggunakan token autentikasi.

---

## **1. Get List Submenu**

**Endpoint:**

    GET /api/setting/submenu/get-list

**Deskripsi:**\
Mengambil daftar submenu dengan opsi pencarian, urutan, dan pagination.

**Query Parameters:**
| Parameter | Tipe | Default | Deskripsi |
| ---------- | ------ | ------------ | -------------------------------------- |
| `order_by` | string | `created_at` | Kolom untuk pengurutan |
| `sort` | string | `asc` | Arah pengurutan (`asc` / `desc`) |
| `page` | int | `1` | Halaman yang ingin diambil |
| `per_page` | int | `10` | Jumlah data per halaman |
| `q` | string | `null` | Pencarian berdasarkan `title` / `name` |

**Contoh Request:**

    GET /api/setting/submenu/get-list?q=user&order_by=title&sort=asc&page=1&per_page=10

**Contoh Response:**

```json
{
    "status": true,
    "message": "Data retrieved successfully",
    "data": [
        {
            "id": 1,
            "menu_id": 2,
            "title": "User Management",
            "icon": "user",
            "url": "/users",
            "menu": {
                "id": 2,
                "title": "Settings"
            }
        }
    ],
    "pagination": {
        "page": 1,
        "per_page": 10,
        "total": 15
    }
}
```

---

## **2. Simpan Submenu**

**Endpoint:**

    POST /api/setting/submenu/simpan

**Deskripsi:**\
Menyimpan data submenu baru atau memperbarui submenu yang sudah ada.

**Body Parameters:**
| Parameter | Tipe | Wajib | Deskripsi |
| ----------- | ------ | ----- | --------------------- |
| `id` | int | Tidak | Jika ada, update data |
| `menu_id` | int | Ya | ID dari menu induk |
| `title` | string | Ya | Judul submenu |
| `icon` | string | Tidak | Icon submenu |
| `url` | string | Tidak | URL submenu |
| `name` | string | Tidak | Nama internal submenu |
| `view` | string | Tidak | View yang digunakan |
| `component` | string | Tidak | Komponen Vue/React |

**Contoh Request:**

```json
{
    "menu_id": 2,
    "title": "User Management",
    "icon": "user",
    "url": "/users",
    "name": "user-management"
}
```

**Contoh Response:**

```json
{
    "status": true,
    "message": "Data submenu berhasil disimpan",
    "data": {
        "id": 1,
        "menu_id": 2,
        "title": "User Management",
        "icon": "user",
        "url": "/users",
        "name": "user-management"
    }
}
```

---

## **3. Hapus Submenu**

**Endpoint:**

    POST /api/setting/submenu/delete

**Deskripsi:**\
Menghapus submenu berdasarkan ID.

**Body Parameters:**
| Parameter | Tipe | Wajib | Deskripsi |
| --------- | ---- | ----- | ---------------------------- |
| `id` | int | Ya | ID submenu yang akan dihapus |

**Contoh Request:**

```json
{
    "id": 1
}
```

**Contoh Response (Sukses):**

```json
{
    "status": true,
    "message": "Data submenu berhasil dihapus",
    "data": {
        "id": 1,
        "title": "User Management"
    }
}
```

**Contoh Response (Gagal):**

```json
{
    "status": false,
    "message": "Data submenu tidak ditemukan"
}
```
