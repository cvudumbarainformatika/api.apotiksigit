<?php

namespace App\Models\Transactions;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beban_h extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];

    public function rincian()
    {
        return $this->hasMany(Beban_r::class, 'notransaksi', 'notransaksi');
    }
}
