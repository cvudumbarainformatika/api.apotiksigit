# API Documentation - Transactions Mutasi

Semua endpoint menggunakan middleware **`auth:sanctum`**  
Prefix:

```
/transactions/mutasi
```

---

## Endpoints

### 1. Get Cabang

**GET** `/get-cabang`  
Controller: `MutasiController@getCabang`

**Response:**

```json
{
    "data": [
        {
            "kodecabang": "APS000",
            "namacabang": "Gudang"
        },
        {
            "kodecabang": "CBG01",
            "namacabang": "Cabang 1"
        }
    ]
}
```

---

### 2. Get Barang

**GET** `/get-barang`  
Controller: `MutasiController@getBarang`

**Query Params:**

-   `q` _(string, optional)_: pencarian berdasarkan nama/kode barang
-   `order_by` _(string, default: nama)_
-   `sort` _(asc|desc, default: asc)_
-   `page` _(int, default: 1)_
-   `per_page` _(int, default: 10)_
-   `depo` _(string, optional)_

**Response:**

```json
{
    "data": [
        {
            "kode": "BRG001",
            "nama": "Paracetamol",
            "stok": [
                {
                    "kode_depo": "APS0001",
                    "jumlah_k": 100
                }
            ]
        }
    ]
}
```

---

### 3. Get List Mutasi

**GET** `/get-list`  
Controller: `MutasiController@index`

**Query Params:**

-   `q` _(string, optional)_: cari berdasarkan kode_mutasi
-   `from` _(date, default: today)_
-   `to` _(date, default: today)_
-   `status` _(null | 1 | 2 | 3 | all)_
-   `tujuan` _(string|null|gudang)_
-   `order_by` _(string, default: created_at)_
-   `sort` _(asc|desc, default: asc)_

**Response:**

```json
{
    "data": [
        {
            "kode_mutasi": "TRX00001",
            "tgl_permintaan": "2025-09-27 10:00:00",
            "dari": "APS0001",
            "tujuan": "APS0000",
            "status": null,
            "rinci": [
                {
                    "kode_barang": "BRG001",
                    "jumlah": 10,
                    "harga_beli": 5000,
                    "satuan_k": "tablet"
                }
            ]
        }
    ],
    "meta": {
        "page": 1,
        "per_page": 10,
        "total": 1
    }
}
```

---

### 4. Simpan Mutasi

**POST** `/simpan`  
Controller: `MutasiController@simpan`

**Body:**

```json
{
    "tgl_permintaan": "2025-09-27",
    "kode_barang": "BRG001",
    "tujuan": "APS0000",
    "jumlah_k": 10,
    "harga_beli": 5000,
    "satuan_k": "tablet",
    "pengirim": "USR001",
    "dari": "APS0001"
}
```

**Response:**

```json
{
    "message": "Data berhasil disimpan",
    "data": {
        "kode_mutasi": "TRX00001",
        "rinci": [
            {
                "kode_barang": "BRG001",
                "jumlah": 10,
                "harga_beli": 5000
            }
        ]
    }
}
```

---

### 5. Hapus Mutasi

**POST** `/delete`  
Controller: `MutasiController@hapus`

**Body:**

```json
{
    "kode_barang": "BRG001",
    "kode_mutasi": "TRX00001"
}
```

**Response:**

```json
{
    "message": "Rincian Obat sudah dihapus"
}
```

---

### 6. Kirim Mutasi

**POST** `/kirim`  
Controller: `MutasiController@kirim`

**Body:**

```json
{
    "kode_mutasi": "TRX00001"
}
```

**Response:**

```json
{
    "message": {
        "kode_mutasi": "TRX00001",
        "status": "1"
    }
}
```

---

### 7. Simpan Distribusi

**POST** `/simpan-distribusi`  
Controller: `MutasiController@simpanDistribusi`

**Body:**

```json
{
    "kode_mutasi": "TRX00001",
    "kode_barang": "BRG001",
    "distribusi": 5,
    "harga_beli": 5000,
    "satuan_k": "tablet"
}
```

**Response:**

```json
{
    "message": {
        "kode_mutasi": "TRX00001",
        "rinci": [
            {
                "kode_barang": "BRG001",
                "distribusi": 5
            }
        ]
    }
}
```

---

### 8. Kirim Distribusi

**POST** `/kirim-distribusi`  
Controller: `MutasiController@kirimDistribusi`

**Body:**

```json
{
    "kode_mutasi": "TRX00001"
}
```

**Response:**

```json
{
    "message": {
        "kode_mutasi": "TRX00001",
        "status": "2"
    }
}
```

---

### 9. Terima Mutasi

**POST** `/terima`  
Controller: `MutasiController@terima`

**Body:**

```json
{
    "kode_mutasi": "TRX00001"
}
```

**Response:**

```json
{
    "message": {
        "kode_mutasi": "TRX00001",
        "status": "3"
    }
}
```

---

## Status Mutasi

-   `null` → Draft (baru dibuat, belum dikirim)
-   `1` → Dikirim
-   `2` → Didistribusikan
-   `3` → Diterima

---
