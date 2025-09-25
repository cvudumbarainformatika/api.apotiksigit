<?php

namespace App\Models\OldApp\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $connection = 'eachy';
    public function kategori()
    {
        return $this->belongsTo(Kategori::class); // kategori_id yang ada di tabel produk itu milik tabel kategori
    }

    public function rak()
    {
        return $this->belongsTo(Rak::class);
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }
    public function satuanBesar()
    {
        return $this->belongsTo(SatuanBesar::class);
    }

    public function merk()
    {
        return $this->belongsTo(Merk::class);
    }
}
