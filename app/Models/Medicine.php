<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'dci',
        'form',
        'dosage',
        'category',
        'archived',
    ];

    protected $casts = [
        'archived' => 'boolean',
    ];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
