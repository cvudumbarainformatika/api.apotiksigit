<?php

namespace App\Models\Master;

use App\Models\Transactions\Beban_r;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beban extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];
    // dipake di laporan laba rugi
    public function rincian()
    {
        return $this->hasMany(Beban_r::class, 'kode_beban', 'id');
    }
}
