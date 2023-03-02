<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_original_name',
        'product_name',
        'product_slug',
        'product_description',
        'product_original_price',
        'product_price',
        'product_image',
        'product_status',
        'product_stock',
        'product_category',
        'product_brand',
        'product_sku',
        'vendor_admin_fee',
        'admin_fee',
        'commission',
        'product_type',
    ];

    protected $appends = ['member_price', 'commission_fee'];

    protected $hidden = ['created_at', 'updated_at'];
    protected $with = ['levelPrices'];

    /**
     * Get all of the levelPrices for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function levelPrices()
    {
        return $this->hasMany(ProductLevelPrice::class);
    }

    // get member price
    public function getMemberPriceAttribute()
    {
        $user_id = auth()->user()->id;
        $member_price = ProductPrice::where('product_id', $this->id)->where('user_id', $user_id)->first();
        if ($member_price) {
            return $member_price->product_price;
        }

        return $this->product_price;
    }

    public function getCommissionFeeAttribute()
    {
        if ($this->product_type == 'pasca') {
            if ($this->admin_fee > 0) {
                $profit = $this->admin_fee - $this->vendor_admin_fee;
                return $profit > 0 ? $profit + $this->commission : 0;
            }
        }


        if ($this->product_price > 0) {
            $profit = $this->product_price - $this->product_original_price;
            return $profit > 0 ? $profit : 0;
        }

        return 0;
    }
}
