<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Fitur 5.1 - List Semua Produk (Bisa diakses Admin & Staff)
     */
    public function index(Request $request)
    {
        // Mengambil query parameter untuk filter, pencarian, dan pagination sesuai kontrak
        $search = $request->query('search');
        $perPage = $request->query('per_page', 10);
        $query = Product::query();

        // Filter berdasarkan nama produk jika ada pencarian
        if ($search) {
            $query->where('nama_produk', 'like', '%' . $search . '%');
        }

        // Ambil data dengan pagination otomatis bawaan Laravel
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage()
            ]
        ], 200);
    }

    /**
     * Fitur 5.3 - Tambah Produk Baru (Hanya Admin Only)
     */
    public function store(Request $request)
    {
        // Validasi hak akses role admin sesuai kontrak
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses (Forbidden)',
                'code' => 'FORBIDDEN'
            ], 403);
        }

        // Validasi input body request sesuai spesifikasi contract
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'deskripsi' => 'nullable|string',
            'harga_jual' => 'required|numeric|min:0',
            'harga_modal' => 'nullable|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang dimasukkan tidak valid',
                'errors' => $validator->errors(),
                'code' => 'VALIDATION_ERROR'
            ], 422);
        }

        // Simpan ke database jika validasi lolos
        $product = Product::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product
        ], 201);
    }

    /**
     * Fitur 5.2 - Detail Produk Tunggal (Bisa diakses Admin & Staff)
     */
    public function show($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Resource tidak ditemukan',
                'code' => 'NOT_FOUND'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product
        ], 200);
    }

    /**
     * Fitur 5.4 - Edit Produk (Hanya Admin Only)
     */
    public function update(Request $request, $id)
    {
        // Validasi hak akses role admin sesuai kontrak
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses (Forbidden)',
                'code' => 'FORBIDDEN'
            ], 403);
        }

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Resource tidak ditemukan',
                'code' => 'NOT_FOUND'
            ], 404);
        }

        // Validasi input edit, SKU unik dikecualikan untuk produk itu sendiri
        $validator = Validator::make($request->all(), [
            'nama_produk' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku,' . $id,
            'deskripsi' => 'nullable|string',
            'harga_jual' => 'required|numeric|min:0',
            'harga_modal' => 'nullable|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Data yang dimasukkan tidak valid',
                'errors' => $validator->errors(),
                'code' => 'VALIDATION_ERROR'
            ], 422);
        }

        // Update data produk ke database
        $product->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui',
            'data' => $product
        ], 200);
    }

    /**
     * Fitur 5.5 - Hapus Produk / Soft Delete (Hanya Admin Only)
     */
    public function destroy(Request $request, $id)
    {
        // Validasi hak akses role admin sesuai kontrak
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses (Forbidden)',
                'code' => 'FORBIDDEN'
            ], 403);
        }

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Resource tidak ditemukan',
                'code' => 'NOT_FOUND'
            ], 404);
        }

        // Melakukan soft delete sesuai kontrak (mengisi deleted_at tanpa menghapus baris dari DB)
        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus'
        ], 200);
    }
}