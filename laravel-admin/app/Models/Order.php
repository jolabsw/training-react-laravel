<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function orederItems() {
        return $this->hasMany(OrderItem::class);
    }

    public function getNameAttribute() {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getTotalAttribute() {
        return $this->orderItems->sum(function (OrderItem $orderItem) {
            return $orderItem->quantity * $orderItem->price;
        });
    }
}
