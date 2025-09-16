# Laporan Kategori Expired API

## Endpoint

```
GET /laporan/kategori-expired/get-list
```

## Deskripsi

Mengambil daftar stok barang yang mendekati atau sudah expired, dengan filter pencarian, tanggal, dan pagination.

---

## Header

| Key             | Value                                       |
| --------------- | ------------------------------------------- |
| `Accept`        | `application/json`                          |
| `Authorization` | `Bearer {token}` (jika menggunakan Sanctum) |

---

## Query Parameters

| Parameter  | Tipe   | Default | Deskripsi                                                                  |
| ---------- | ------ | ------- | -------------------------------------------------------------------------- |
| `q`        | string | null    | Pencarian berdasarkan `nama` atau `kode` barang                            |
| `sort`     | string | `asc`   | Urutan berdasarkan tanggal expired (`tgl_exprd`). Nilai: `asc` atau `desc` |
| `page`     | int    | `1`     | Halaman data                                                               |
| `per_page` | int    | `10`    | Jumlah data per halaman                                                    |
| `from`     | date   | null    | Filter tanggal mulai (`tgl_exprd >= from`)                                 |
| `to`       | date   | null    | Filter tanggal akhir (`tgl_exprd < to`)                                    |

---

## Contoh Request

```
GET /laporan/kategori-expired/get-list?from=2025-08-01&to=2025-08-31&q=Paracetamol&sort=desc&page=1&per_page=10
```

---

## Response (200 OK)

```json
{
    "success": true,
    "message": "Data retrieved successfully",
    "meta": {
        "page": 1,
        "per_page": 10,
        "total": 150
    },
    "data": [
        {
            "kode": "OBT001",
            "nama": "Paracetamol 500mg",
            "satuan_k": "Tablet",
            "satuan_b": "Strip",
            "jumlah_k": 120,
            "isi": 10,
            "tgl_exprd": "2025-08-15"
        },
        {
            "kode": "OBT002",
            "nama": "Amoxicillin 500mg",
            "satuan_k": "Kapsul",
            "satuan_b": "Strip",
            "jumlah_k": 80,
            "isi": 10,
            "tgl_exprd": "2025-08-20"
        }
    ]
}
```

---

## Catatan

-   Jika `from` atau `to` **tidak diisi**, maka filter tanggal **tidak diterapkan**.
-   Data diurutkan berdasarkan `tgl_exprd` sesuai parameter `sort`.
