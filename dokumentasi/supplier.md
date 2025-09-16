## Master Supplier

-   general route `api/v1/master/supplier`

-   get `/get-list` payload :

    -   q
    -   per_page
    -   page

-   post `Simpan` : `/simpan` payload :

    -   kode `null / '' jika baru`
    -   nama `divalidasi`
    -   tlp
    -   bank
    -   rekening
    -   alamat `'text'`

-   post `Hapus` : `/delete` payload
    -   id
