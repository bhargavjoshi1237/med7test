<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Lunar\Models\Order;


class RefundRequest extends Model
{
    protected $guarded = [];

    /**
     * The order that this refund request belongs to.
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * The user who made the refund request.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
