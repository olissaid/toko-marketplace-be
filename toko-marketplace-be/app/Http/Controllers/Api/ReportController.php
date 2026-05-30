<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Fitur 9.1 - Laporan Penjualan Sederhana (Hanya Admin Only)
     * Endpoint: GET /api/laporan/penjualan
     */
    public function salesReport(Request $request)
    {
        // 1. Validasi hak akses role admin sesuai kontrak
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses (Forbidden)',
                'code' => 'FORBIDDEN'
            ], 403);
        }

        $periode = $request->query('periode', 'harian'); // harian | bulanan | custom
        $tanggal = $request->query('tanggal', date('Y-m-d'));
        $bulan = $request->query('bulan', date('Y-m'));
        $tanggalDari = $request->query('tanggal_dari');
        $tanggalSampai = $request->query('tanggal_sampai');

        // Base Query untuk mengambil orderan yang sukses ('dikirim' atau 'selesai')
        $orderQuery = Order::whereIn('status', ['dikirim', 'selesai']);

        // Filter berdasarkan periode sesuai query parameter contract
        if ($periode === 'harian') {
            $orderQuery->whereDate('created_at', $tanggal);
            $labelPeriode = $tanggal;
        } elseif ($periode === 'bulanan') {
            $orderQuery->whereMonth('created_at', substr($bulan, 5, 2))
                       ->whereYear('created_at', substr($bulan, 0, 4));
            $labelPeriode = $bulan;
        } elseif ($periode === 'custom' && $tanggalDari && $tanggalSampai) {
            $orderQuery->whereBetween(DB::raw('DATE(created_at)'), [$tanggalDari, $tanggalSampai]);
            $labelPeriode = $tanggalDari . ' s/d ' . $tanggalSampai;
        } else {
            $orderQuery->whereDate('created_at', date('Y-m-d'));
            $labelPeriode = date('Y-m-d');
        }

        $orders = $orderQuery->get();

        // 2. Hitung Rekapitulasi Total Penjualan, Modal, dan Keuntungan Sederhana
        $totalPesanan = $orders->count();
        $totalPenjualan = $orders->sum('total_harga');
        
        $totalModal = 0;
        foreach ($orders as $order) {
            foreach ($order->orderDetails as $detail) {
                // Mengambil snapshot harga modal dari produk terkait saat ini
                $hargaModalProduk = $detail->product ? $detail->product->harga_modal : 0;
                $totalModal += $detail->jumlah * $hargaModalProduk;
            }
        }
        
        $totalKeuntungan = $totalPenjualan - $totalModal;

        // 3. Kalkulasi penjualan per marketplace (Shopee vs Tokopedia)
        $omzetShopee = $orders->where('marketplace', 'shopee')->sum('total_harga');
        $omzetTokopedia = $orders->where('marketplace', 'tokopedia')->sum('total_harga');

        // 4. Cari Produk Terlaris (Top Selling) pada periode tersebut
        $orderIds = $orders->pluck('id');
        $produkTerlaris = DB::table('order_details')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->select(
                'order_details.product_id',
                'products.nama_produk',
                DB::raw('SUM(order_details.jumlah) as total_terjual'),
                DB::raw('SUM(order_details.subtotal) as total_omzet')
            )
            ->whereIn('order_details.order_id', $orderIds)
            ->groupBy('order_details.product_id', 'products.nama_produk')
            ->orderBy('total_terjual', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'periode' => $labelPeriode,
                'total_pesanan' => $totalPesanan,
                'total_penjualan' => (int) $totalPenjualan,
                'total_modal' => (int) $totalModal,
                'total_keuntungan' => (int) $totalKeuntungan,
                'produk_terlaris' => $produkTerlaris->map(function($p) {
                    return [
                        'produk_id' => $p->product_id,
                        'nama_produk' => $p->nama_produk,
                        'total_terjual' => (int) $p->total_terjual,
                        'total_omzet' => (int) $p->total_omzet
                    ];
                }),
                'penjualan_per_marketplace' => [
                    'shopee' => (int) $omzetShopee,
                    'tokopedia' => (int) $omzetTokopedia
                ]
            ]
        ], 200);
    }

    /**
     * Fitur 9.2 - Dashboard Summary (Bisa diakses Admin & Staff)
     * Endpoint: GET /api/dashboard
     */
    public function dashboardSummary(Request $request)
    {
        $userRole = $request->user()->role;

        // Hitung total data produk aktif dan stok yang menipis (< 10)
        $totalProduk = Product::count();
        $stokMenipis = Product::where('stok', '<', 10)->count();

        // Hitung data pesanan hari ini berdasarkan statusnya
        $pesananHariIni = Order::whereDate('created_at', date('Y-m-d'))->count();
        $pesananDiproses = Order::where('status', 'diproses')->count();
        $pesananDikirim = Order::where('status', 'dikirim')->count();

        // Hitung Omzet Penjualan Hari Ini
        $omzetHariIni = Order::whereIn('status', ['dikirim', 'selesai'])
            ->whereDate('created_at', date('Y-m-d'))
            ->sum('total_harga');

        // Hitung Omzet Penjualan Bulan Ini (BUSINESS RULE: Sembunyikan bernilai null jika yang login adalah Staff!)
        $omzetBulanIni = null;
        if ($userRole === 'admin') {
            $omzetBulanIni = Order::whereIn('status', ['dikirim', 'selesai'])
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->sum('total_harga');
            $omzetBulanIni = (int) $omzetBulanIni;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_produk' => $totalProduk,
                'stok_menipis' => $stokMenipis,
                'pesanan_hari_ini' => $pesananHariIni,
                'pesanan_diproses' => $pesananDiproses,
                'pesanan_dikirim' => $pesananDikirim,
                'omzet_hari_ini' => (int) $omzetHariIni,
                'omzet_bulan_ini' => $omzetBulanIni
            ]
        ], 200);
    }
}