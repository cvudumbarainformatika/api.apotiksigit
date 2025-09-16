# 📄 Dokumentasi API BarangController

Base URL: `/api/v1/master/barang`

---

## 🔷 Get Barang (List)

**GET** `/api/v1/master/barang/get-list`

> Mengambil daftar barang dengan pencarian, sorting, dan pagination.

### Query Parameters

| Parameter | Type   | Required | Default      | Notes                                                     |
| --------- | ------ | -------- | ------------ | --------------------------------------------------------- |
| q         | string | ❌       | -            | Kata kunci untuk pencarian (`nama`, `kode`)               |
| order_by  | string | ❌       | `created_at` | Kolom untuk sorting, contoh: `nama`, `kode`, `created_at` |
| sort      | string | ❌       | `asc`        | Arah sorting: `asc` atau `desc`                           |
| per_page  | int    | ❌       | 15           | Jumlah item per halaman                                   |
| page      | int    | ❌       | 1            | Halaman yang diambil                                      |

### Contoh Request

```
GET /api/v1/master/barang/get-list?q=paracetamol&order_by=nama&sort=asc&per_page=10&page=1
```

### Response (200)

```json
{
    "data": [
        {
            "id": 1,
            "kode": "BRG0001",
            "nama": "Paracetamol",
            "satuan_k": "pcs",
            "satuan_b": "dus",
            "isi": "4",
            "kandungan": "apapun itu",
            "harga_jual_resep_k": "8000",
            "harga_jual_biasa_k": "6000",
            "created_at": "...",
            "updated_at": "..."
        }
    ],
    "meta": {
        "current_page": 1,
        "from": 1,
        "last_page": 3,
        "path": "http://example.com/api/v1/master/barang/get-list",
        "per_page": 10,
        "to": 10,
        "total": 25
    }
}
```

### 📌 Simpan Barang

**POST** `/api/v1/master/barang/simpan`

> Tambah atau update barang.

Menambahkan barang baru atau mengupdate barang jika kode sudah ada.

| Field              | Type    | Required | Notes                                            |
| ------------------ | ------- | -------- | ------------------------------------------------ |
| nama               | string  | ✅       | Nama barang                                      |
| kode               | string  | ❌       | Jika tidak dikirim, akan dibuat otomatis         |
| satuan_k           | string  | ❌       | opsional, satuan kecil                           |
| satuan_b           | string  | ❌       | opsional, satuan besar                           |
| isi                | int     | ❌       | opsional, isi satuan besar terhadap satuan kecil |
| kandungan          | string  | ❌       | opsional, isi kandungan obat                     |
| harga_jual_resep_k | decimal | ❌       | opsional, harga jual                             |
| harga_jual_biasa_k | decimal | ❌       | opsional, harga jual                             |

#### Contoh Request

```json
{
    "nama": "Paracetamol",
    "satuan_k": "kotak",
    "isi": 10,
    "harga_jual_biasa_k": 5000
}
```

#### Response

```json
{
    "data": {
        "id": 1,
        "kode": "BRG0001",
        "nama": "Paracetamol",
        "satuan_k": "kotak",
        "satuan_b": null,
        "isi": 10,
        "kandungan": null,
        "harga_jual_biasa_k": 5000,
        "harga_jual_biasa_k": 0
    },
    "message": "Data barang berhasil disimpan"
}
```

---

### 📌 Hapus Barang

**POST** `/api/v1/master/barang/delete`

> Hapus barang berdasarkan `id`.

#### Request Body

| Field | Type | Required |
| ----- | ---- | -------- |
| id    | int  | ✅       |

#### Contoh Request

```json
{
    "id": 1
}
```

#### Response Sukses

```json
{
    "data": {
        "id": 1,
        "kode": "BRG0001",
        "nama": "Paracetamol"
    },
    "message": "Data barang berhasil dihapus"
}
```

#### Response Gagal

```json
{
    "message": "Data barang tidak ditemukan"
}
```

---

### 🔷 Catatan

✅ Semua response error validasi (422) otomatis Laravel:

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field": ["Pesan error di sini."]
    }
}
```
