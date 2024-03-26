<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cloth extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function clothImages()
    {
        return $this->hasMany(ClothImage::class);
    }
}
