<?php

namespace App\Models\Setting;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HakAkses extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];
    public function items()
    {
        return $this->hasMany(Menu::class, 'id', 'menu_id');
    }
}
