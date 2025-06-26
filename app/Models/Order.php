<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'branch_id', 'customer_id', 'order_date', 'order_time',
        'order_status', 'therapist_gender', 'guest_gender',
        'guest_phone_number', 'confirmation_datetime', 'therapist_id',
    ];

    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->transaction_id)) {
                $order->transaction_id = (string) str()->ulid();
            }
            $now = Carbon::now();

            // Set the order_date if it's not already set
            if (empty($order->order_date)) {
                // Laravel will automatically format this to Y-m-d for the 'date' column
                $order->order_date = $now;
            }

            // Set the order_time if it's not already set
            if (empty($order->order_time)) {
                // Laravel will automatically format this to H:i:s for the 'time' column
                $order->order_time = $now;
            }

            $order->order_status = "Pending";

        });
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function therapist()
    {
        return $this->belongsTo(Therapist::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class);
    }

}
