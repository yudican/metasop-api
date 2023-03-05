<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLevelPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'level_price_id',
        'price',
    ];

    protected $appends = [
        'level_name',
        'product_name',
        'commission',
    ];

    public function levelPrices()
    {
        return $this->belongsTo(LevelPrice::class, 'level_price_id');
    }

    public function products()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function getLevelNameAttribute()
    {
        $levelPrice = LevelPrice::find($this->level_price_id);
        return $levelPrice ? $levelPrice->level_name : '-';
    }

    public function getProductNameAttribute()
    {
        $product = Product::find($this->product_id);
        return $product ? $product->product_name : '-';
    }

    public function getCommissionAttribute()
    {
        $product = Product::find($this->product_id);
        if ($product) {
            if ($product->type == 'prepaid') {
                $commission = $this->price - $product->product_price;
                return $commission > 0 ? $commission : 0;
            }

            $commission = $this->price - $product->admin_fee;
            return $commission > 0 ? $commission : 0;
        }

        return 0;
    }
}
