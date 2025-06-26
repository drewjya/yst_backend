<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    //
    protected $fillable = [
        "order_id",
        "service_name",
        "service_price",
        "duration",
        "service_id",
    ];
}
