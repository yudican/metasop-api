<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    protected $fillable = [
        'banner_title',
        'banner_url',
        'banner_image',
        'banner_description',
        'status',
    ];

    protected $appends = [
        'banner_image_url',
    ];

    protected $hidden = ['created_at', 'updated_at'];

    public function getBannerImageUrlAttribute()
    {
        return $this->banner_image ? asset('images/banner/' . $this->banner_image) : null;
    }
}
