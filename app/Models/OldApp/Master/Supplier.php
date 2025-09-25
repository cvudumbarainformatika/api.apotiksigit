<?php

namespace App\Models\OldApp\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $connection = 'eachy';
    public function perusahaan()
    {
        return $this->belongsTo(Perusahaan::class);
    }
}
