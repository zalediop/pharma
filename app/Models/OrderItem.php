<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'medicine_id',
        'quantity',
        'unit_price',
    ];

    public function order()
    {
        return $this->belongsTo(PurchaseOrder::class, 'order_id');
    }

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }
}
