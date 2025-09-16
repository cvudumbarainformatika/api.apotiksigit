# 📄 Dokumentasi API ReturPenjualanController

Base URL: `/api/v1/transactions/returpenjualan`

## 🔷 Daftar Endpoint

1. **GET** `/get-list` - Mendapatkan daftar retur penjualan
2. **GET** `/get-penjualan` - Mendapatkan daftar transaksi penjualan (untuk referensi retur)
3. **POST** `/simpan` - Menyimpan/memperbarui data retur penjualan
4. **POST** `/lock-retur-penjualan` - Mengunci data retur penjualan
5. **POST** `/open-lock-retur-penjualan` - Membuka kunci data retur penjualan (saat ini disable)
6. **POST** `/delete` - Menghapus data retur penjualan
7. **POST** `/delete-rinci` - Menghapus rincian retur penjualan yang belum dikunci

## 🔷 Status Flag Retur Penjualan

| Nilai Flag | Keterangan     |
| ---------- | -------------- |
| null       | Belum Terkunci |
| 1          | Sudah Terkunci |

## 1. Mendapatkan Daftar Retur Penjualan

**GET** `/api/transactions/returpenjualan/get-list`

### Query Parameters

| Parameter | Type   | Required | Default      | Description                                                        |
| --------- | ------ | -------- | ------------ | ------------------------------------------------------------------ |
| q         | string | ❌       | -            | Keyword pencarian (noretur, nopenerimaan, nofaktur, nama supplier) |
| order_by  | string | ❌       | `created_at` | Kolom untuk sorting                                                |
| sort      | string | ❌       | `asc`        | Arah sorting (`asc` atau `desc`)                                   |
| page      | int    | ❌       | 1            | Nomor halaman                                                      |
| per_page  | int    | ❌       | 10           | Jumlah item per halaman                                            |

### Response Sukses (200)

```json
{
    "data": [
        {
            "id": 1,
            "noretur": "RPJ000001",
            "nopenjualan": "TRX000012",
            "tgl_retur": "2025-08-09 13:07:10",
            "kode_user": "USR000001",
            "flag": null,
            "created_at": "2025-08-09T12:41:01.000000Z",
            "updated_at": "2025-08-09T13:07:10.000000Z",
            "retur_penjualan_r": [
                {
                    "id": 1,
                    "noretur": "RPJ000001",
                    "kode_barang": "BRG000012",
                    "nopenjualan": "TRX000012",
                    "nopenerimaan": "PN0001",
                    "nobatch": "NB0004",
                    "jumlah_k": "15.00",
                    "satuan_k": "pill",
                    "harga": "30000.00",
                    "kode_user": "USR000001",
                    "returpenjualan_h_id": 1,
                    "returpenjualan_h_noretur": "RPJ000001",
                    "created_at": "2025-08-09T12:41:02.000000Z",
                    "updated_at": "2025-08-09T13:06:01.000000Z",
                    "master_barang": {
                        "nama": "Zinc Tablet",
                        "kode": "BRG000012",
                        "satuan_k": "strip",
                        "satuan_b": null,
                        "isi": 10,
                        "kandungan": "20mg"
                    }
                }
            ]
        }
    ],
    "meta": {
        "first": "...",
        "last": "...",
        "prev": null,
        "next": null,
        "current_page": 1,
        "per_page": 10,
        "total": 1,
        "last_page": 1,
        "from": 1,
        "to": 1
    }
}
```

## 2. Mendapatkan Daftar Transaksi Penjualan

**GET** `/api/transactions/returpenjualan/get-penjualan`

### Query Parameters

| Parameter | Type   | Required | Default      | Description                                  |
| --------- | ------ | -------- | ------------ | -------------------------------------------- |
| q         | string | ❌       | -            | Keyword pencarian (nopenjualan, nama barang) |
| order_by  | string | ❌       | `created_at` | Kolom untuk sorting                          |
| sort      | string | ❌       | `asc`        | Arah sorting (`asc` atau `desc`)             |
| page      | int    | ❌       | 1            | Nomor halaman                                |
| per_page  | int    | ❌       | 10           | Jumlah item per halaman                      |

### Response Sukses (200)

```json
{
    "data": [
        {
            "nopenjualan": "TRX000012",
            "rinci": [
                {
                    "kode_barang": "BRG000012",
                    "master": {
                        "nama": "Zinc Tablet",
                        "kode": "BRG000012",
                        "satuan_k": "strip",
                        "satuan_b": null,
                        "isi": 10,
                        "kandungan": "20mg"
                    }
                }
            ]
        }
    ],
    "meta": {
        "...": "..."
    }
}
```

## 3. Menyimpan/Memperbarui Retur Penjualan

**POST** `/api/transactions/returpenjualan/simpan`

> ⚠️ Catatan: Jika nopenjualan sudah pernah diretur dengan flag=1, maka akan ditolak

### Request Body

| Parameter           | Type   | Required | Description                       |
| ------------------- | ------ | -------- | --------------------------------- |
| noretur             | string | ❌       | Nomor retur (kosong jika baru)    |
| tgl_retur           | date   | ❌       | Tanggal retur (default: sekarang) |
| nopenjualan         | string | ✔️       | Nomor penjualan                   |
| nopenerimaan        | string | ✔️       | Nomor penerimaan                  |
| kode_supplier       | string | ✔️       | Kode supplier                     |
| kode_barang         | string | ✔️       | Kode barang                       |
| nobatch             | string | ✔️       | Nomor batch                       |
| jumlah_k            | number | ✔️       | Jumlah dalam satuan kecil         |
| satuan_k            | string | ✔️       | Satuan kecil                      |
| harga               | number | ✔️       | Harga barang                      |
| id_stok             | int    | ✔️       | ID stok                           |
| id_penerimaan_rinci | int    | ✔️       | ID rincian penerimaan             |

### Response Sukses (201)

```json
{
    "success": true,
    "data": {
        "id": 1,
        "noretur": "RPJ000001",
        "nopenjualan": "TRX000012",
        "nopenerimaan": "PN0001",
        "tgl_retur": "2025-08-09 13:07:10",
        "kode_supplier": "SUP002",
        "kode_user": "USR000001",
        "flag": null,
        "created_at": "...",
        "updated_at": "...",
        "retur_penjualan_r": [
            {
                "id": 1,
                "noretur": "RPJ000001",
                "kode_barang": "BRG000012",
                "nopenjualan": "TRX000012",
                "nobatch": "NB0004",
                "jumlah_k": "15.00",
                "satuan_k": "pill",
                "harga": "30000.00",
                "kode_user": "USR000001",
                "returpenjualan_h_id": 1,
                "returpenjualan_h_noretur": "RPJ000001",
                "created_at": "...",
                "updated_at": "...",
                "master_barang": {
                    "nama": "Zinc Tablet",
                    "kode": "BRG000012",
                    "satuan_k": "strip",
                    "satuan_b": null,
                    "isi": 10,
                    "kandungan": "20mg"
                }
            }
        ]
    },
    "message": "Data Retur Penjualan berhasil disimpan"
}
```

### Error Responses

-   **401 Unauthorized**: User tidak login
-   **410 Gone**:
    -   Data retur sudah terkunci
    -   Nomor Penjualan ini sudah pernah di retur
    -   Data penerimaan tidak ditemukan
    -   Gagal menyimpan data

## 4. Mengunci Retur Penjualan

**POST** `/api/transactions/returpenjualan/lock-retur-penjualan`

### Request Body

| Parameter | Type   | Required | Description |
| --------- | ------ | -------- | ----------- |
| noretur   | string | ✔️       | Nomor retur |

### Response Sukses (201)

```json
{
    "data": {
        "...": "..."
    },
    "message": "Data Retur Penjualan berhasil dikunci"
}
```

> ⚠️ Catatan: Saat mengunci data:
>
> -   Stok akan ditambah sesuai jumlah retur
> -   Header penjualan akan diupdate flag=2
> -   Harus memiliki minimal 1 rincian

### Error Responses

-   **401 Unauthorized**: User tidak login
-   **410 Gone**:
    -   Data retur tidak ditemukan
    -   Data sudah terkunci
    -   Data bukan data draft (flag bukan null)

## 5. Membuka Kunci Retur Penjualan

**POST** `/api/transactions/returpenjualan/open-lock-retur-penjualan`

### Request Body

| Parameter | Type   | Required | Description |
| --------- | ------ | -------- | ----------- |
| noretur   | string | ✔️       | Nomor retur |

### Response Sukses (201)

```json
{
    "success": true,
    "data": {
        "...": "..."
    },
    "message": "Kunci Data Retur Penjualan berhasil dibuka"
}
```

### Error Responses

-   **401 Unauthorized**: User tidak login
-   **410 Gone**:
    -   Data retur tidak ditemukan
    -   Data belum terkunci
    -   Data bukan data retur terkunci (flag bukan 1)

## 6. Menghapus Retur Penjualan

**POST** `/api/transactions/returpenjualan/delete`

### Request Body

| Parameter | Type   | Required | Description | Validation |
| --------- | ------ | -------- | ----------- | ---------- |
| noretur   | string | ✔️       | Nomor retur | required   |

### Response Sukses (200)

```json
{
    "success": true,
    "message": "Data retur penjualan berhasil dihapus"
}
```

### Error Responses

-   **400 Bad Request**: Nomor retur tidak ditemukan
-   **401 Unauthorized**: User tidak login
-   **410 Gone**:
    -   Data retur tidak ditemukan
    -   Data sudah terkunci

## 7. Menghapus Rincian Retur Penjualan (Belum Dikunci)

**POST** `/api/transactions/returpenjualan/delete-rinci`

### Request Body

| Parameter   | Type   | Required | Description | Validation |
| ----------- | ------ | -------- | ----------- | ---------- |
| noretur     | string | ✔️       | Nomor retur | required   |
| kode_barang | string | ✔️       | Kode barang | required   |

> ⚠️ Catatan: Jika rincian yang dihapus adalah yang terakhir, header retur juga akan dihapus otomatis

### Response Sukses (200)

```json
{
    "success": true,
    "data": {
        "id": 1,
        "noretur": "RPJ000001",
        "kode_barang": "BRG000012",
        "nopenjualan": "TRX000012",
        "nobatch": "NB0004",
        "jumlah_k": "15.00",
        "satuan_k": "pill",
        "harga": "30000.00",
        "kode_user": "USR000001",
        "returpenjualan_h_id": 1,
        "returpenjualan_h_noretur": "RPJ000001",
        "created_at": "...",
        "updated_at": "..."
    },
    "message": "Data rincian retur berhasil dihapus"
}
```

### Error Responses

-   **400 Bad Request**: Parameter tidak lengkap
-   **401 Unauthorized**: User tidak login
-   **410 Gone**:
    -   Data retur tidak ditemukan
    -   Data sudah terkunci
    -   Rincian tidak ditemukan
