<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $table = 'stock_movements';

    protected $fillable = [
        'product_id',
        'tipe',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'keterangan',
        'referensi_order_id',
        'created_by',
    ];

    /**
     * Relasi ke model User (Siapa yang melakukan mutasi stok)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    /**
     * Relasi ke model Product
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}