# API Dokumentasi – Stok Opname

## Base URL

```
/api/transactions/opname
```

## Middleware

-   `auth:sanctum` (wajib autentikasi)

---

## Endpoints

### 1. GET /get-list

Ambil daftar stok opname berdasarkan bulan dan tahun tertentu.

#### URL

```
GET /api/transactions/opname/get-list
```

#### Query Parameters

| Parameter  | Tipe   | Default        | Deskripsi                                                            |
| ---------- | ------ | -------------- | -------------------------------------------------------------------- |
| `order_by` | string | `created_at`   | Kolom untuk pengurutan.                                              |
| `sort`     | string | `asc`          | Arah pengurutan (`asc` / `desc`).                                    |
| `page`     | int    | `1`            | Nomor halaman.                                                       |
| `per_page` | int    | `10`           | Jumlah data per halaman.                                             |
| `bulan`    | int    | bulan sekarang | Bulan opname.                                                        |
| `tahun`    | int    | tahun sekarang | Tahun opname.                                                        |
| `q`        | string | null           | Pencarian berdasarkan `nopenerimaan`, `noorder`, atau `nama barang`. |

#### Contoh Request

```
GET /api/transactions/opname/get-list?bulan=7&tahun=2025&q=Paracetamol
```

#### Contoh Response

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "kode_barang": "OBT001",
            "tgl_opname": "2025-07-31",
            "created_at": "2025-08-01T08:00:00",
            "updated_at": "2025-08-01T08:00:00",
            "barang": {
                "kode": "OBT001",
                "nama": "Paracetamol",
                "satuan_k": "Tablet",
                "satuan_b": "Box"
            }
        }
    ],
    "pagination": {
        "page": 1,
        "per_page": 10,
        "total": 25
    }
}
```

---

### 2. POST /simpan

Menyimpan data stok opname untuk bulan tertentu.

#### URL

```
POST /api/transactions/opname/simpan
```

#### Body Parameters

| Parameter | Tipe   | Wajib | Deskripsi     |
| --------- | ------ | ----- | ------------- |
| `tahun`   | int    | ✅    | Tahun opname. |
| `bulan`   | string | ✅    | Bulan opname. |

#### Validasi

-   `tahun` dan `bulan` **wajib diisi**.
-   `bulan` **harus sebelum bulan ini**, kalau tidak → response **410**.

#### Response Status

| Status | Deskripsi                                                                    |
| ------ | ---------------------------------------------------------------------------- |
| `200`  | Berhasil menyimpan stok opname.                                              |
| `410`  | Tidak valid (bulan >= bulan ini) atau sudah ada opname di tanggal yang sama. |

#### Contoh Request

```json
{
    "tahun": 2025,
    "bulan": 07
}
```

#### Contoh Response Berhasil

```json
{
  "hasil": true,
  "data": [
    {
      "nopenerimaan": "PNR-001",
      "kode_barang": "OBT001",
      "jumlah_k": 100,
      "tgl_opname": "2025-07-31 23:59:59"
    }
  ],
  "barang": [...],
  "tglOpnameTerakhir": "2025-06-30 23:59:59",
  "akhirBulanLalu": "2025-07-31 23:59:59"
}
```

#### Contoh Response Gagal (Bulan Tidak Valid)

```json
{
    "message": "Transaksi Opname hanya bisa di lakukan di bulan lalu"
}
```

#### Contoh Response Gagal (Duplikat)

```json
{
    "message": "Sudah ada opname di tanggal yang sama"
}
```

---

### Proses Bisnis (simpan)

1. Validasi bulan harus **sebelum bulan ini**.
2. Cek apakah sudah ada opname di akhir bulan target.
3. Ambil semua **barang** beserta:
    - **Stok awal**
    - **Penerimaan**
    - **Penjualan**
    - **Retur penjualan**
    - **Retur pembelian**
    - **Penyesuaian**
4. Hitung sisa stok untuk setiap `id_penerimaan_rinci`.
5. Simpan data opname ke tabel `stok_opnames`.
