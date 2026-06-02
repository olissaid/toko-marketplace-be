<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StockController extends Controller
{
    /**
     * Fitur 7.1 - List Riwayat Stok Produk (Admin & Staff)
     * Endpoint: GET /api/produk/{id}/stok
     */
    public function index(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Resource tidak ditemukan',
                'code' => 'NOT_FOUND'
            ], 404);
        }

        // Ambil riwayat movement stok khusus untuk produk ini
        $perPage = $request->query('per_page', 20);
        $movements = StockMovement::with('user')
            ->where('product_id', $id)
            ->latest()
            ->paginate($perPage);

        $formattedRiwayat = collect($movements->items())->map(function($move) {
            return [
                'id' => $move->id,
                'tipe' => $move->tipe, // masuk | keluar
                'jumlah' => $move->jumlah,
                'stok_sebelum' => $move->stok_sebelum,
                'stok_sesudah' => $move->stok_sesudah,
                'keterangan' => $move->keterangan,
                'referensi_pesanan_id' => $move->referensi_order_id,
                'created_by' => $move->user ? $move->user->name : 'System',
                'created_at' => $move->created_at->toISOString()
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'produk_id' => $product->id,
                'nama_produk' => $product->nama_produk,
                'stok_saat_ini' => $product->stok,
                'riwayat' => $formattedRiwayat
            ],
            'meta' => [
                'current_page' => $movements->currentPage(),
                'per_page' => $movements->perPage(),
                'total' => $movements->total()
            ]
        ], 200);
    }

    /**
     * Fitur 7.2 - Tambah Stok / Restock Manual (Admin & Staff)
     */
    public function storeMasuk(Request $request, $id)
    {
        // Validasi input body request sesuai kontrak
        $validator = Validator::make($request->all(), [
            'jumlah' => 'required|integer|min:1',
            'keterangan' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang dimasukkan tidak valid',
                'errors' => $validator->errors(),
                'code' => 'VALIDATION_ERROR'
            ], 422);
        }

        // Gunakan DB Transaction agar proses aman
        DB::beginTransaction();

        try {
            // Cari produk dan kunci barisnya untuk mencegah race condition
            $product = Product::lockForUpdate()->find($id);

            if (!$product) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Resource tidak ditemukan',
                    'code' => 'NOT_FOUND'
                ], 404);
            }

            $stokSebelum = $product->stok;
            $stokSesudah = $stokSebelum + $request->jumlah;

            // 1. Update jumlah stok di tabel products
            $product->update(['stok' => $stokSesudah]);

            // 2. Catat historis pergerakan stok ke tabel stock_movements
            StockMovement::create([
                'product_id' => $product->id,
                'tipe' => 'masuk',
                'jumlah' => $request->jumlah,
                'stok_sebelum' => $stokSebelum,
                'stok_sesudah' => $stokSesudah,
                'keterangan' => $request->keterangan,
                'referensi_order_id' => null, // Null karena ini restock manual, bukan dari pesanan
                'created_by' => $request->user()->id // ID Admin/Staff yang sedang login
            ]);

            DB::commit(); // Simpan permanen ke database

            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil ditambahkan',
                'data' => [
                    'produk_id' => $product->id,
                    'stok_sebelum' => $stokSebelum,
                    'jumlah_masuk' => (int) $request->jumlah,
                    'stok_sesudah' => $stokSesudah
                ]
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan internal server',
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }
}