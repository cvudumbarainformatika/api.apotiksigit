<?php

namespace App\Models\Transactions;

use App\Models\Master\Cabang;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MutasiHeader extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];
    protected $hidden = ['updated_at', 'created_at'];
    public function rinci()
    {
        return $this->hasMany(MutasiRequest::class, 'kode_mutasi', 'kode_mutasi');
    }
    public function dari()
    {
        return $this->belongsTo(Cabang::class, 'dari', 'kodecabang');
    }
    public function tujuan()
    {
        return $this->belongsTo(Cabang::class, 'tujuan', 'kodecabang');
    }
}
