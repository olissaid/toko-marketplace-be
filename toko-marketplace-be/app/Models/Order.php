<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // Menentukan nama tabel secara eksplisit
    protected $table = 'orders';

    /**
     * Mass Assignment Protection
     * Daftar kolom yang diizinkan untuk diisi secara massal via Order::create()
     */
    protected $fillable = [
        'nomor_pesanan',
        'marketplace',
        'nama_pembeli',
        'no_hp_pembeli',
        'alamat_pengiriman',
        'status',
        'total_harga',
        'catatan',
        'created_by',
    ];

    /**
     * Relasi Eloquent: One-to-Many
     * Satu pesanan (Order) memiliki banyak item detail (OrderDetail)
     */
    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }

    /**
     * Relasi Eloquent: BelongsTo
     * Pesanan ini diinput oleh seorang User (Admin/Staff)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}