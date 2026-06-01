<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_id',
        'quantity',
        'expiry_date',
        'alert_threshold',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function medicine()
    {
        return $this->belongsTo(Medicine::class);
    }

    public function scopeExpiringWithin($query, $days)
    {
        return $query->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }
}
