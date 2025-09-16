# 📦 API Dokumentasi - Transactions / Stok

Semua endpoint menggunakan prefix:

```
transactions/stok
```

Autentikasi: `Bearer Token` (Sanctum)

---

## 1. Get List Stok

**Endpoint**

```
GET /get-list
```

**Query Params**
| Nama | Default | Deskripsi |
|-----------|-------------|-----------|
| q | null | Pencarian berdasarkan `nopenerimaan`, `noorder`, atau `nama barang` |
| order_by | created_at | Kolom untuk sorting |
| sort | asc | Urutan sorting (`asc` / `desc`) |
| page | 1 | Nomor halaman |
| per_page | 10 | Jumlah data per halaman |
| tampil | semua/null | Jika tidak `semua` maka hanya menampilkan stok dengan `jumlah_k > 0` |

**Response**

```json
{
    "data": [
        {
            "id": 1,
            "kode_barang": "BRG001",
            "jumlah_k": 50,
            "barang": {
                "kode": "BRG001",
                "nama": "Paracetamol 500mg"
            }
        }
    ],
    "meta": {
        "total": 120,
        "page": 1,
        "per_page": 10
    }
}
```

---

## 2. Simpan Penyesuaian Stok

**Endpoint**

```
POST /simpan
```

**Body (JSON)**
| Field | Type | Required | Deskripsi |
|--------------|---------|----------|-----------|
| kode_barang | string | ✔ | Kode barang |
| id_stok | int | ✔ | ID stok yang disesuaikan |
| jumlah | int | ✔ | Jumlah penyesuaian (+/-) |
| satuan_k | string | ✔ | Satuan (misalnya `tablet`, `botol`) |
| keterangan | string | ✔ | Catatan penyesuaian |

**Response Berhasil**

```json
{
    "message": "Penyesuaian sudah dibuat, dan stok sudah di sesuaikan",
    "data": {
        "id": 15,
        "kode_barang": "BRG001",
        "jumlah_sebelum": 50,
        "jumlah_setelah": 60,
        "jumlah_k": 10,
        "satuan_k": "tablet",
        "tgl_penyesuaian": "2025-08-20 10:15:23"
    }
}
```

**Response Gagal (contoh)**

```json
{
    "message": "Stok tidak ditemukan, gagal membuat penyesuaian",
    "file": "/app/Http/Controllers/Api/Transactions/StokController.php",
    "line": 85
}
```

---

## 3. Kartu Stok (Rekap per Barang)

**Endpoint**

```
GET /get-kartu-stok
```

**Query Params**
| Nama | Default | Deskripsi |
|-----------|---------|-----------|
| q | null | Pencarian berdasarkan nama/kode barang |
| from | awal bulan berjalan (00:00:00) | Periode awal |
| to | hari ini (23:59:59) | Periode akhir |
| order_by | created_at | Kolom sorting |
| sort | asc | Urutan sorting |
| page | 1 | Nomor halaman |
| per_page | 10 | Jumlah data per halaman |

**Response**

```json
{
    "data": [
        {
            "kode": "BRG001",
            "nama": "Paracetamol 500mg",
            "stok_awal": { "jumlah_k": 50 },
            "stok": { "jumlah_k": 100 },
            "penyesuaian": { "jumlah_k": 10 },
            "penjualan_rinci": { "jumlah_k": 20 },
            "retur_penjualan_rinci": { "jumlah_k": 5 },
            "penerimaan_rinci": { "jumlah_k": 70 },
            "retur_pembelian_rinci": { "jumlah_k": 2 }
        }
    ],
    "meta": {
        "total": 25,
        "page": 1,
        "per_page": 10
    }
}
```

---

## 4. Kartu Stok Rinci (Detail per Barang)

**Endpoint**

```
GET /get-rinci-kartu-stok
```

**Query Params**
| Nama | Default | Deskripsi |
|-----------|---------|-----------|
| id | - | ✔ ID barang |
| from | awal bulan berjalan (00:00:00) | Periode awal |
| to | hari ini (23:59:59) | Periode akhir |

**Response**

```json
{
    "data": {
        "id": 1,
        "kode": "BRG001",
        "nama": "Paracetamol 500mg",
        "stok_awal": [{ "tgl_opname": "2025-07-31", "jumlah_k": 50 }],
        "stok": [{ "id": 123, "jumlah_k": 100 }],
        "penyesuaian": [
            { "jumlah_k": 10, "tgl_penyesuaian": "2025-08-15 09:23:00" }
        ],
        "penjualan_rinci": [
            {
                "nopenjualan": "PJ0001",
                "tgl_penjualan": "2025-08-02",
                "jumlah_k": 5
            }
        ],
        "retur_penjualan_rinci": [
            { "noretur": "RT001", "tgl_retur": "2025-08-03", "jumlah_k": 2 }
        ],
        "penerimaan_rinci": [
            {
                "nopenerimaan": "PN001",
                "tgl_penerimaan": "2025-08-01",
                "jumlah_k": 70
            }
        ],
        "retur_pembelian_rinci": [
            { "noretur": "RP001", "tglretur": "2025-08-05", "jumlah_k": 1 }
        ]
    },
    "req": {
        "id": 1,
        "from": "2025-08-01 00:00:00",
        "to": "2025-08-20 23:59:59"
    }
}
```

---

✍️ Catatan:

-   Endpoint `kartuStok` lebih cocok untuk **rekap banyak barang**
-   Endpoint `kartuStokRinci` lebih cocok untuk **detail satu barang**
