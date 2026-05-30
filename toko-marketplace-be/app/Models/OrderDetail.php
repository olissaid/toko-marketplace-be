<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit sesuai database contract
    protected $table = 'order_details';

    /**
     * Mass Assignment Protection
     * Daftar kolom yang diizinkan untuk diisi secara massal via OrderDetail::create()
     */
    protected $fillable = [
        'order_id',
        'product_id',
        'jumlah',
        'harga_satuan',
        'subtotal',
    ];

    /**
     * Relasi Eloquent: BelongsTo
     * Item detail ini merujuk ke sebuah Order utama
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    /**
     * Relasi Eloquent: BelongsTo
     * Item detail ini merujuk ke produk yang dibeli
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}