<?php

namespace App\Models\Transactions;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiHeader extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];
    public function rinci()
    {
        return $this->hasMany(MutasiRequest::class, 'kode_mutasi', 'kode_mutasi');
    }
}
