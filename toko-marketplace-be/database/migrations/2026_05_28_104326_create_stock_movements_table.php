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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id(); // BIGINT PK Auto increment
            
            // Foreign Key (FK) yang menghubungkan ke tabel products
            $table->foreignId('product_id')->constrained('products'); // BIGINT FK 
            
            // Tipe pergerakan stok: masuk (restock) atau keluar (karena pesanan)
            $table->enum('tipe', ['masuk', 'keluar']); // ENUM 
            
            $table->integer('jumlah'); // INT Jumlah stok yang bergerak 
            $table->integer('stok_sebelum'); // INT Snapshot stok sebelum aksi 
            $table->integer('stok_sesudah'); // INT Snapshot stok sesudah aksi 
            $table->string('keterangan'); // STRING Penjelasan (misal: "Restock dari supplier") [cite: 207, 263]
            
            // Foreign Key (FK) opsional ke tabel orders. 
            // Diisi jika stok berkurang karena pesanan masuk, bernilai null jika karena restock biasa.
            $table->foreignId('referensi_order_id')->nullable()->constrained('orders')->onDelete('set null'); // BIGINT FK 
            
            // Foreign Key (FK) ke tabel users untuk mencatat siapa admin/staff yang melakukan aksi ini
            $table->foreignId('created_by')->constrained('users'); // BIGINT FK 
            
            $table->timestamps(); // Kolom created_at dan updated_at 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};