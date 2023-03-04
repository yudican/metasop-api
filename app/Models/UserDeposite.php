<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDeposite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'payment_code',
        'deposit_amount',
        'ref',
        'status',
        'paymentUrl',
        'payment_name',
        'payment_fee',
        'trx_id'
    ];

    protected $appends = [
        'user_name',
        'status_name',
        'status_color'
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getUserNameAttribute()
    {
        $user = User::find($this->user_id);
        return $user ? $user->name : '-';
    }

    public function getStatusNameAttribute()
    {
        $status = [
            0 => 'Waiting Payment',
            1 => 'Success',
            2 => 'Canceled',
            3 => 'Failed',
            4 => 'Expired',
        ];

        return $status[$this->status];
    }

    public function getStatusColorAttribute()
    {
        $status = [
            0 => 'yellow',
            1 => 'green',
            2 => 'red',
            3 => 'red',
            4 => 'red',
        ];

        return $status[$this->status];
    }
}
