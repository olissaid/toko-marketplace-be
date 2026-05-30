<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. MEMBUAT DATA USER DUMMY (Sesuai Aturan Login & Role)
        
        // Akun untuk Admin Toko (Akses Penuh)
        User::create([
            'name' => 'Admin Toko',
            'email' => 'admin@toko.com',
            'password' => Hash::make('password123'), // Password di-hash menggunakan Bcrypt
            'role' => 'admin',
        ]);

        // Akun untuk Staff Gudang (Akses Terbatas)
        User::create([
            'name' => 'Staff Gudang',
            'email' => 'staff@toko.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
        ]);


        // 2. MEMBUAT DATA PRODUK AWAL (Sesuai Aturan Manajemen Produk)
        
        Product::create([
            'nama_produk' => 'Kaos Polos Putih',
            'sku' => 'KPP-001',
            'deskripsi' => 'Kaos polos bahan katun combed 30s',
            'harga_jual' => 75000,
            'harga_modal' => 45000,
            'stok' => 120,
            'satuan' => 'pcs',
        ]);

        Product::create([
            'nama_produk' => 'Kaos Polos Hitam',
            'sku' => 'KPH-002',
            'deskripsi' => 'Kaos hitam combed kualitas premium',
            'harga_jual' => 75000,
            'harga_modal' => 45000,
            'stok' => 10, // Sengaja disetting dikit untuk ngetest alert stok menipis nanti
            'satuan' => 'pcs',
        ]);

        Product::create([
            'nama_produk' => 'Kemeja Flanel Kotak',
            'sku' => 'KFK-003',
            'deskripsi' => 'Kemeja flanel lengan panjang motif kotak',
            'harga_jual' => 120000,
            'harga_modal' => 80000,
            'stok' => 45,
            'satuan' => 'pcs',
        ]);
    }
}