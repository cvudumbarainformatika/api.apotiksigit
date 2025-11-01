<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pendapatanlain_h extends Model
{
    use HasFactory;
    protected $table = 'pendapatanlain_h';
    protected $guarded = ['id'];

    public function rincian()
    {
        return $this->hasMany(Pendapatanlain_r::class, 'notrans', 'notrans');
    }
}
