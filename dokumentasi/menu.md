# Menu API Documentation

Base URL:

    /api/setting/menu

Authentication:\

-   Menggunakan **Sanctum** middleware (`auth:sanctum`). - Semua endpoint
    **wajib** menggunakan token autentikasi.

---

## **1. Get List Menu**

**Endpoint:**

    GET /api/setting/menu/get-list

**Deskripsi:**\
Mengambil daftar menu dengan opsi pencarian, urutan, dan pagination.

**Query Parameters:**
| Parameter | Tipe | Default | Deskripsi |
| --------- | ------ | ----------- | ----------------------------------------------------------- |
| q | string | null | Kata kunci untuk pencarian berdasarkan `title` atau `name`. |
| order_by | string | created_at | Kolom pengurutan (`id`, `title`, `created_at`). |
| sort | string | asc | Arah pengurutan (`asc` atau `desc`). |
| page | int | 1 | Nomor halaman. |
| per_page | int | 10 | Jumlah data per halaman. |

**Contoh Request:**

    GET /api/setting/menu/get-list?q=dashboard&order_by=title&sort=asc&page=1&per_page=10

**Contoh Response:**

```json
{
    "status": true,
    "message": "Data retrieved successfully",
    "data": [
        {
            "id": 1,
            "title": "Dashboard",
            "icon": "home",
            "url": "/dashboard",
            "children": []
        }
    ],
    "pagination": {
        "page": 1,
        "per_page": 10,
        "total": 35
    }
}
```

---

## **2. Simpan Menu**

**Endpoint:**

    POST /api/setting/menu/simpan

**Deskripsi:**\
Menyimpan data menu baru atau memperbarui menu yang sudah ada.

**Body Parameters:**
| Parameter | Tipe | Wajib | Deskripsi |
| --------- | ------ | ----- | ----------------------- |
| id | int | tidak | ID menu (untuk update). |
| title | string | ya | Judul menu. |
| icon | string | tidak | Nama ikon menu. |
| url | string | tidak | URL menu. |
| name | string | tidak | Nama unik menu. |
| view | string | tidak | Nama view terkait. |
| component | string | tidak | Nama komponen frontend. |

**Contoh Request:**

```json
{
    "title": "Dashboard",
    "icon": "home",
    "url": "/dashboard",
    "name": "dashboard"
}
```

**Contoh Response:**

```json
{
    "status": true,
    "message": "Data menu berhasil disimpan",
    "data": {
        "id": 1,
        "title": "Dashboard",
        "icon": "home",
        "url": "/dashboard",
        "name": "dashboard"
    }
}
```

---

## **3. Hapus Menu**

**Endpoint:**

    POST /api/setting/menu/delete

**Deskripsi:**\
Menghapus menu berdasarkan ID.

**Body Parameters:**
| Parameter | Tipe | Wajib | Deskripsi |
| --------- | ---- | ----- | -------------------------- |
| id | int | ya | ID menu yang akan dihapus. |

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
    "message": "Data menu berhasil dihapus",
    "data": {
        "id": 1,
        "title": "Dashboard"
    }
}
```

**Contoh Response (Gagal):**

```json
{
    "status": false,
    "message": "Data menu tidak ditemukan"
}
```
