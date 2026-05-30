<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('order_details', function (Blueprint $table) {
            $table->id(); // BIGINT PK Auto increment
            
            // Foreign Key (FK) yang menghubungkan detail ini ke tabel utama orders
            // Jika data order utama dihapus, detailnya otomatis ikut terhapus (onDelete cascade)
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade'); // BIGINT FK 
            
            // Foreign Key (FK) yang menghubungkan item ini ke tabel products
            $table->foreignId('product_id')->constrained('products'); // BIGINT FK 
            
            $table->integer('jumlah'); // INT Jumlah barang yang dibeli (Min: 1) 
            $table->decimal('harga_satuan', 15, 2); // DECIMAL(15,2) Snapshot harga saat order dibuat 
            $table->decimal('subtotal', 15, 2); // DECIMAL(15,2) Hasil kalkulasi (jumlah * harga_satuan) 
            
            $table->timestamps(); // Kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_details');
    }
};