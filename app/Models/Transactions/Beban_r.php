<?php

namespace App\Models\Transactions;

use App\Models\Master\Beban;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beban_r extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];

    public function mbeban()
    {
        return $this->belongsTo(Beban::class, 'kode_beban', 'id');
    }
}
