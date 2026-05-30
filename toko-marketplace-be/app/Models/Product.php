<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; // Wajib diimport untuk fitur soft delete

class Product extends Model
{
    use HasFactory, SoftDeletes; // Mengaktifkan soft delete sesuai api contract

    // Menentukan nama tabel di database secara eksplisit
    protected $table = 'products';

    /**
     * Mass Assignment Protection
     * Daftar kolom yang diizinkan untuk diisi secara massal via Product::create() atau $product->update()
     */
    protected $fillable = [
        'nama_produk',
        'sku',
        'deskripsi',
        'harga_jual',
        'harga_modal',
        'stok',
        'satuan',
    ];

    /**
     * Kolom yang otomatis diubah menjadi objek Carbon/Tanggal oleh Laravel
     */
    protected $dates = ['deleted_at'];
}