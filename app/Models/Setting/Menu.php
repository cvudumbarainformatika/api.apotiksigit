<?php

namespace App\Models\Setting;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    use HasFactory, LogsActivity;
    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];


    public function children()
    {
        return $this->hasMany(Submenu::class, 'menu_id', 'id');
    }
}
