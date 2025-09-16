# 📄 Dokumentasi API PenjualanController

Base URL: `/api/transactions/penjualan`

---

## 🔷 Get List Obat

**GET** `/get-list-obat`

## Deskripsi

Fungsi `getListObat` digunakan untuk mengambil daftar obat dari tabel `Barang` dengan fitur pencarian (`search`), pengurutan (`sorting`), dan paginasi manual.  
Data yang diambil juga memuat informasi stok yang tersedia (`stok`) dan rincian penjualan yang belum selesai (`penjualanRinci`).

## Parameter Request

Fungsi ini membaca parameter dari query string request HTTP.

| Parameter  | Tipe    | Default | Deskripsi                                                  |
| ---------- | ------- | ------- | ---------------------------------------------------------- |
| `order_by` | string  | `nama`  | Kolom yang digunakan untuk pengurutan data.                |
| `sort`     | string  | `asc`   | Arah pengurutan (`asc` atau `desc`).                       |
| `page`     | integer | `1`     | Nomor halaman (tidak digunakan di query, hanya disiapkan). |
| `per_page` | integer | `10`    | Jumlah data yang diambil.                                  |
| `q`        | string  | _null_  | Kata kunci pencarian pada kolom `nama` atau `kode`.        |

## Alur Kerja

1. **Ambil Parameter Request**
    - Parameter `order_by`, `sort`, `page`, dan `per_page` diambil dari request, dengan nilai default jika tidak diberikan.
2. **Query Data Barang**

    - Mengambil data dari model `Barang`.
    - Jika parameter `q` ada, maka dilakukan filter:
        ```php
        where('nama', 'like', "%q%")
        orWhere('kode', 'like', "%q%")
        ```

3. **Relasi Data**

    - **Relasi `stok`**:
        - Hanya stok dengan `jumlah_k > 0` yang diambil.
    - **Relasi `penjualanRinci`**:
        - Mengambil `kode_barang`, `jumlah_k`, `id_stok` dari tabel `penjualan_r_s`.
        - Melakukan `LEFT JOIN` dengan `penjualan_h_s` berdasarkan `nopenjualan`.
        - Hanya mengambil data yang `penjualan_h_s.flag` bernilai `NULL` (penjualan belum selesai).

4. **Pengurutan & Batas Data**

    - Data diurutkan sesuai parameter `order_by` dan `sort`.
    - Data dibatasi sesuai parameter `per_page`.

5. **Response**
    - Mengembalikan data dalam format JSON:
        ```json
        {
          "data": [ ... ]
        }
        ```

### Response Success (200)

```json
{
    "data": [
        {
            "nama": "Paracetamol",
            "kode": "OBT001",
            "harga_jual_resep_k": "8000",
            "harga_jual_biasa_k": "6000",
            "id_penerimaan_rinci": 1,
            "id_stok": 1,
            "harga_beli": "4000",
            "satuan_k": "Tablet",
            "satuan_b": "Box",
            "isi": 10,
            "nobatch": "B001",
            "tgl_exprd": "2025-01-01",
            "stok": [
                {
                    "jumlah_k": 20
                }
            ],
            "penjualan_rinci": [
                {
                    "kode_barang": "OBT001",
                    "jumlah_k": 2,
                    "id_stok": 123
                }
            ]
        }
    ]
}
```

---

## 🔷 Get List Penjualan

**GET** `/get-list`

> Mengambil daftar transaksi penjualan dengan filter tanggal.

### Query Parameters

| Parameter | Type   | Required | Default      | Notes                                |
| --------- | ------ | -------- | ------------ | ------------------------------------ |
| q         | string | ❌       | -            | Kata kunci pencarian                 |
| order_by  | string | ❌       | `created_at` | Kolom untuk sorting                  |
| sort      | string | ❌       | `asc`        | Arah sorting: `asc` atau `desc`      |
| per_page  | int    | ❌       | 10           | Jumlah item per halaman              |
| page      | int    | ❌       | 1            | Halaman yang diambil                 |
| from      | date   | ❌       | today        | Filter tanggal awal (format: Y-m-d)  |
| to        | date   | ❌       | today        | Filter tanggal akhir (format: Y-m-d) |

### Response Success (200)

```json
{
    "data": [
        {
            "id": 1,
            "nopenjualan": "TRX0001",
            "tgl_penjualan": "2024-01-01 14:30:00",
            "kode_pelanggan": "PLG001",
            "kode_dokter": "DOK001",
            "cara_bayar": "TUNAI",
            "flag": null,
            "diskon": 0,
            "jumlah_bayar": 0,
            "kembali": 0,
            "rinci": [
                {
                    "id": 1,
                    "nopenjualan": "TRX0001",
                    "kode_barang": "OBT001",
                    "jumlah_k": 10,
                    "jumlah_b": 1,
                    "harga_jual": 5000,
                    "harga_beli": 4000,
                    "subtotal": 50000,
                    "master": {
                        "nama": "Paracetamol",
                        "kode": "OBT001",
                        "satuan_k": "Tablet",
                        "satuan_b": "Box",
                        "isi": 10,
                        "kandungan": "500mg"
                    }
                }
            ]
        }
    ],
    "links": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": "..."
    },
    "meta": {
        "current_page": 1,
        "from": 1,
        "path": "...",
        "per_page": 10,
        "to": 10,
        "total": 100
    }
}
```

---

## 📌 Simpan Penjualan

**POST** `/simpan`

### Body Parameters

| Field               | Type   | Required | Notes                           |
| ------------------- | ------ | -------- | ------------------------------- |
| nopenjualan         | string | ❌       | Auto-generated jika tidak diisi |
| tgl_penjualan       | date   | ❌       | Default: waktu sekarang         |
| kode_pelanggan      | string | ❌       | -                               |
| kode_dokter         | string | ❌       | -                               |
| kode_barang         | string | ✅       | Kode obat yang dijual           |
| jumlah_k            | int    | ✅       | Jumlah dalam satuan kecil       |
| satuan_k            | string | ❌       | -                               |
| satuan_b            | string | ❌       | -                               |
| isi                 | int    | ✅       | Isi per satuan besar            |
| harga_jual          | int    | ✅       | Harga jual per satuan           |
| harga_beli          | int    | ✅       | Harga beli per satuan           |
| id_penerimaan_rinci | int    | ✅       | ID rincian penerimaan stok      |
| nopenerimaan        | string | ✅       | Nomor penerimaan stok           |
| nobatch             | string | ✅       | Nomor batch obat                |
| tgl_exprd           | date   | ✅       | Tanggal kadaluarsa              |
| id_stok             | int    | ✅       | ID stok obat                    |

### Response Created (201)

```json
{
    "message": "Data berhasil disimpan",
    "data": {
        "nopenjualan": "TRX0001",
        "tgl_penjualan": "2024-01-01 14:30:00",
        "kode_pelanggan": "PLG001",
        "kode_dokter": "DOK001",
        "kode_user": "USR001",
        "cara_bayar": "",
        "rinci": [...]
    }
}
```

---

## 📌 Pembayaran Penjualan

**POST** `/bayar`

> Proses pembayaran transaksi penjualan.

### Body Parameters

| Field        | Type   | Required | Notes                       |
| ------------ | ------ | -------- | --------------------------- |
| id           | int    | ✅       | ID header penjualan         |
| diskon       | int    | ❌       | Nilai diskon                |
| jumlah_bayar | int    | ✅       | Jumlah uang yang dibayarkan |
| kembali      | int    | ❌       | Uang kembalian              |
| cara_bayar   | string | ✅       | Metode pembayaran           |

### Response Success (200)

```json
{
    "message": "Pembayaran berhasil dilakukan",
    "data": {
        "id": 1,
        "nopenjualan": "TRX0001",
        "tgl_penjualan": "2024-01-01 14:30:00",
        "kode_pelanggan": "PLG001",
        "kode_dokter": "DOK001",
        "cara_bayar": "TUNAI",
        "flag": "1",
        "diskon": 0,
        "jumlah_bayar": 50000,
        "kembali": 0,
        "rinci": [
            {
                "id": 1,
                "nopenjualan": "TRX0001",
                "kode_barang": "OBT001",
                "jumlah_k": 10,
                "jumlah_b": 1,
                "harga_jual": 5000,
                "harga_beli": 4000,
                "subtotal": 50000,
                "master": {
                    "nama": "Paracetamol",
                    "kode": "OBT001",
                    "satuan_k": "Tablet",
                    "satuan_b": "Box",
                    "isi": 10,
                    "kandungan": "500mg"
                }
            }
        ]
    }
}
```

### Catatan Tambahan

✅ Saat pembayaran berhasil:

-   Status transaksi (`flag`) akan diubah menjadi "1"
-   Stok obat akan otomatis berkurang sesuai jumlah pembelian
-   Jika kembalian yang dikirim tidak sesuai perhitungan, sistem akan menghitung ulang

---

## 📌 Hapus Rincian

**POST** `/hapus`

### Body Parameters

| Field       | Type   | Required | Notes                                  |
| ----------- | ------ | -------- | -------------------------------------- |
| nopenjualan | string | ✅       | Nomor transaksi penjualan yang dihapus |
| kode_barang | string | ✅       | Kode barang yang dihapus               |

### Response Success (200)

```json
{
    "message": "Data Obat sudah dihapus"
}
```

---

### 🔷 Catatan

✅ Semua endpoint membutuhkan autentikasi Sanctum.

✅ Response error validasi (422):

```json
{
    "message": "The given data was invalid.",
    "errors": {
        "field": ["Pesan error di sini."]
    }
}
```

✅ Response error sistem (410):

```json
{
    "message": "Pesan error",
    "file": "...",
    "line": "...",
    "trace": [...]
}
```
