# API Documentation - Profile Toko

## Base URL

    /api/master/profile-toko

**Middleware:** `auth:sanctum`

---

## **1. Get Profile Toko**

### **Endpoint**

    GET /api/master/profile-toko/get-profile

### **Description**

Mengambil data profile toko pertama yang tersedia di tabel
`profile_toko`.

### **Response**

#### **200 OK**

```json
{
    "data": {
        "id": 1,
        "nama": "Toko ABC",
        "alamat": "Jl. Contoh No.123",
        "telepon": "08123456789",
        "pemilik": "Budi",
        "header": "Header Nota",
        "footer": "Footer Nota",
        "created_at": "2025-08-26T10:00:00.000000Z",
        "updated_at": "2025-08-26T10:00:00.000000Z"
    }
}
```

---

## **2. Simpan / Update Profile Toko**

### **Endpoint**

    POST /api/master/profile-toko/simpan

### **Description**

Menyimpan atau memperbarui data profile toko. Jika belum ada data, akan
membuat baru. Jika sudah ada, akan update data pertama.

### **Request Body**

**Content-Type:** `application/json`

Field Type Required Description

---

nama string Yes Nama toko
alamat string No Alamat toko
telepon string No Nomor telepon
pemilik string No Nama pemilik
header string No Header nota
footer string No Footer nota

#### **Contoh Request**

```json
{
    "nama": "Toko ABC",
    "alamat": "Jl. Contoh No.123",
    "telepon": "08123456789",
    "pemilik": "Budi",
    "header": "Header Nota",
    "footer": "Footer Nota"
}
```

### **Response**

#### **200 OK**

```json
{
    "message": "Data sudah di simpan",
    "data": {
        "id": 1,
        "nama": "Toko ABC",
        "alamat": "Jl. Contoh No.123",
        "telepon": "08123456789",
        "pemilik": "Budi",
        "header": "Header Nota",
        "footer": "Footer Nota",
        "created_at": "2025-08-26T10:00:00.000000Z",
        "updated_at": "2025-08-26T10:10:00.000000Z"
    }
}
```

### **Validation Errors**

#### **422 Unprocessable Entity**

```json
{
    "errors": {
        "nama": ["Nama wajib diisi."]
    }
}
```

---

### **Model yang Digunakan**

**`App\Models\Master\ProfileToko`**

---

## **Catatan**

-   Jika data profile toko belum ada, akan dibuat baru.
-   Jika sudah ada, hanya akan update data pertama (menggunakan
    `updateOrCreate`).
