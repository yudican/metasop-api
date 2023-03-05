<?php

namespace App\Models;

use DateTime;
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
        'status_color',
        'expired_payment',
        'created_payment',
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
            0 => '#f1c40f',
            1 => '#2ecc71',
            2 => '#e74c3c',
            3 => '#e74c3c',
            4 => '#e74c3c',
        ];

        return $status[$this->status];
    }

    public function getExpiredPaymentAttribute()
    {
        $start_date = date('Y-m-d H:i:s');
        $end_date = $this->created_at->addDays(1);

        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $diff = $start->diff($end);

        $daysInSecs = $diff->format('%r%a') * 24 * 60 * 60;
        $hoursInSecs = $diff->h * 60 * 60;
        $minsInSecs = $diff->i * 60;

        $seconds = $daysInSecs + $hoursInSecs + $minsInSecs + $diff->s;

        return $seconds * 1000;
    }

    public function getCreatedPaymentAttribute()
    {
        return date('d M Y H:i', strtotime($this->created_at)) . ' WIB';
    }
}
