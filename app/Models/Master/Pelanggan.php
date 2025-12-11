<?php

namespace App\Models\Master;

use App\Models\FailedToSend;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];
    protected $hidden = ['updated_at', 'created_at'];
    // cek failed simpan di kirim master ke cabang
    public function failed()
    {
        return $this->hasMany(FailedToSend::class, 'kode', 'kode')->where('model', 'pelanggan');
    }
}
