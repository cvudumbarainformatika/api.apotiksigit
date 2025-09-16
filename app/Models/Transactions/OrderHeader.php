<?php

namespace App\Models\Transactions;

use App\Models\Master\Supplier;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderHeader extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];


    // Define any relationships or methods specific to OrderHeader here
    public function orderRecords()
    {
        return $this->hasMany(OrderRecord::class, 'nomor_order', 'nomor_order');
    }
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'kode_supplier', 'kode');
    }

    public function penerimaan()
    {
        return $this->belongsTo(Penerimaan_h::class,'nomor_order', 'noorder');
    }
}
