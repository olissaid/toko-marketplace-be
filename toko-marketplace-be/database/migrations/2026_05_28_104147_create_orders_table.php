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
        Schema::create('orders', function (Blueprint $table) {
            $table->id(); // BIGINT PK Auto increment [cite: 259]
            
            // Nomor pesanan unik yang digenerate otomatis oleh sistem
            $table->string('nomor_pesanan', 50)->unique(); // VARCHAR(50) Unique [cite: 259]
            
            // Jenis marketplace asal pesanan (shopee, tokopedia, lainnya)
            $table->enum('marketplace', ['shopee', 'tokopedia', 'lainnya']); // ENUM [cite: 259]
            
            // Data pembeli dan pengiriman
            $table->string('nama_pembeli', 255); // VARCHAR(255) [cite: 259]
            $table->string('no_hp_pembeli', 20)->nullable(); // VARCHAR(20) Nullable [cite: 259]
            $table->text('alamat_pengiriman'); // TEXT [cite: 259]
            
            // Status pesanan (diproses | dikirim | selesai)
            $table->enum('status', ['diproses', 'dikirim', 'selesai']); // ENUM [cite: 259]
            
            // Total harga seluruh pesanan (kalkulasi otomatis dari BE)
            $table->decimal('total_harga', 15, 2); // DECIMAL(15,2) [cite: 259]
            
            // Catatan tambahan dari pembeli jika ada
            $table->text('catatan')->nullable(); // TEXT Nullable [cite: 259]
            
            // Foreign Key (FK) ke tabel users untuk mencatat siapa yang menginput
            $table->foreignId('created_by')->constrained('users'); // BIGINT FK ke users.id [cite: 259]
            
            $table->timestamps(); // Kolom created_at dan updated_at [cite: 259]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};