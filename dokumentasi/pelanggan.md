## Master Pelanggan

-   general route `api/v1/master/pelanggan`

-   get `/get-list` payload :

    -   q
    -   per_page
    -   page

-   post `Simpan` : `/simpan` payload :

    -   kode `null / '' jika baru`
    -   nama `divalidasi`
    -   tlp
    -   alamat `'text'`

-   post `Hapus` : `/delete` payload
    -   id
