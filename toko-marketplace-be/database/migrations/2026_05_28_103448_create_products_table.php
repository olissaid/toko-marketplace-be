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
        Schema::create('products', function (Blueprint $table) {
            $table->id(); // BIGINT PK Auto increment [cite: 257]
            $table->string('nama_produk', 255); // VARCHAR(255) [cite: 257]
            $table->string('sku', 100)->unique(); // VARCHAR(100) Unique [cite: 257]
            $table->text('deskripsi')->nullable(); // TEXT Nullable [cite: 257]
            $table->decimal('harga_jual', 15, 2); // DECIMAL(15,2) [cite: 257]
            $table->decimal('harga_modal', 15, 2)->nullable(); // DECIMAL(15,2) Nullable [cite: 257]
            $table->integer('stok')->default(0); // INT Default 0 [cite: 257]
            $table->string('satuan', 50); // VARCHAR(50) contoh: pcs/kg/lusin/dll [cite: 257]
            
            // Fitur soft delete (menandai produk dihapus tanpa membuang dari DB) 
            $table->softDeletes(); // Kolom deleted_at [cite: 257]
            
            $table->timestamps(); // Kolom created_at dan updated_at [cite: 255, 257]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};