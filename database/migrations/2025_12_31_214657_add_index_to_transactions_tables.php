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
        Schema::table('stoks', function (Blueprint $table) {
            $table->index(['kode_barang', 'kode_depo'], 'idx_stoks_kodebarang_kodedepo');
        });
        Schema::table('stok_opnames', function (Blueprint $table) {
            $table->index(
                ['kode_barang', 'kode_depo', 'tgl_opname'],
                'idx_stokop_barang_depo_tgl'
            );
        });
        Schema::table('stok_opnames', function (Blueprint $table) {
            $table->index('tgl_opname', 'idx_stok_opnames_tgl');
        });
        Schema::table('retur_penjualan_rs', function (Blueprint $table) {
            $table->index('noretur', 'idx_retur_rs_noretur');
            $table->index('nopenjualan', 'idx_retur_rs_nopenjualan');
            $table->index('kode_barang', 'idx_retur_rs_kode_barang');
        });
        Schema::table('retur_penjualan_hs', function (Blueprint $table) {
            $table->index('noretur', 'idx_retur_hs_noretur');
            $table->index('nopenjualan', 'idx_retur_hs_nopenjualan');
            $table->index('tgl_retur', 'idx_retur_hs_tgl_retur');
            $table->index('flag', 'idx_retur_hs_flag');
        });
        Schema::table('retur_pembelian_rs', function (Blueprint $table) {
            $table->index('noretur', 'idx_retur_pembelian_rs_noretur');
            $table->index('kode_barang', 'idx_retur_pembelian_rs_kode_barang');
        });
        Schema::table('retur_pembelian_hs', function (Blueprint $table) {
            $table->index('noretur', 'idx_retur_pembelian_hs_noretur');
            $table->index('tglretur', 'idx_retur_pembelian_hs_tglretur');
            $table->index('kode_supplier', 'idx_retur_pembelian_hs_kode_supplier');
            $table->index('flag', 'idx_retur_pembelian_hs_flag');
        });
        Schema::table('penyesuaians', function (Blueprint $table) {
            $table->index(
                ['kode_depo', 'tgl_penyesuaian'],
                'idx_penyesuaian_depo_tgl'
            );
            $table->index(
                ['kode_barang', 'kode_depo', 'tgl_penyesuaian'],
                'idx_penyesuaian_barang_depo_tgl'
            );
        });
        Schema::table('penjualan_r_s', function (Blueprint $table) {
            $table->index('nopenjualan', 'idx_penjualan_rs_nopenjualan');
            $table->index('kode_barang', 'idx_penjualan_rs_kode_barang');
        });
        Schema::table('penjualan_h_s', function (Blueprint $table) {
            $table->index('nopenjualan', 'idx_penjualan_hs_nopenjualan');
            $table->index('tgl_penjualan', 'idx_penjualan_hs_tgl_penjualan');
            $table->index('kode_pelanggan', 'idx_penjualan_hs_kode_pelanggan');
            $table->index('kode_dokter', 'idx_penjualan_hs_kode_dokter');
            $table->index('kode_user', 'idx_penjualan_hs_kode_user');
            $table->index('cara_bayar', 'idx_penjualan_hs_cara_bayar');
            $table->index('flag', 'idx_penjualan_hs_flag');
            $table->index('flag_setor', 'idx_penjualan_hs_flag_setor');
        });
        Schema::table('penerimaan_rs', function (Blueprint $table) {
            $table->index('nopenerimaan', 'idx_penerimaan_rs_nopenerimaan');
            $table->index('noorder', 'idx_penerimaan_rs_noorder');
            $table->index('kode_barang', 'idx_penerimaan_rs_kode_barang');
            $table->index('tgl_exprd', 'idx_penerimaan_rs_tgl_exprd');
        });
        Schema::table('penerimaan_hs', function (Blueprint $table) {
            $table->index('nopenerimaan', 'idx_penerimaan_hs_nopenerimaan');
            $table->index('noorder', 'idx_penerimaan_hs_noorder');
            $table->index('tgl_penerimaan', 'idx_penerimaan_hs_tgl_penerimaan');
            $table->index('nofaktur', 'idx_penerimaan_hs_nofaktur');
            $table->index('tgl_faktur', 'idx_penerimaan_hs_tgl_faktur');
            $table->index('kode_suplier', 'idx_penerimaan_hs_kode_suplier');
        });
        Schema::table('pendapatanlain_r', function (Blueprint $table) {
            $table->index('notrans', 'idx_pendapatanlain_r_notrans');
        });
        Schema::table('pendapatanlain_h', function (Blueprint $table) {
            $table->index('notrans', 'idx_pendapatanlain_h_notrans');
            $table->index('tgl', 'idx_pendapatanlain_h_tgl');
            $table->index('dari', 'idx_pendapatanlain_h_dari');
        });
        Schema::table('pembayaran_hutangs', function (Blueprint $table) {
            $table->index('nopelunasan', 'idx_pembayaran_hutangs_nopelunasan');
            $table->index('tgl_pelunasan', 'idx_pembayaran_hutangs_tgl_pelunasan');
            $table->index('flag', 'idx_pembayaran_hutangs_flag');
            $table->index(
                ['flag', 'tgl_pelunasan'],
                'idx_pembayaran_hutangs_flag_tgl'
            );
        });
        Schema::table('pembayaran_hutang_rincis', function (Blueprint $table) {
            $table->index('nopelunasan', 'idx_pembayaran_hutang_rincis_nopelunasan');
            $table->index('noorder', 'idx_pembayaran_hutang_rincis_noorder');
            $table->index('nopenerimaan', 'idx_pembayaran_hutang_rincis_nopenerimaan');
            $table->index('nofaktur', 'idx_pembayaran_hutang_rincis_nofaktur');
            $table->index('kode_suplier', 'idx_pembayaran_hutang_rincis_kode_suplier');
        });
        Schema::table('order_records', function (Blueprint $table) {
            $table->index('nomor_order', 'idx_order_records_nomor_order');
            $table->index('kode_barang', 'idx_order_records_kode_barang');
        });
        Schema::table('order_headers', function (Blueprint $table) {
            $table->index('nomor_order', 'idx_order_headers_nomor_order');
            $table->index('tgl_order', 'idx_order_headers_tgl_order');
            $table->index('kode_depo', 'idx_order_headers_kode_depo');
            $table->index('flag', 'idx_order_headers_flag');
            $table->index('status_penerimaan', 'idx_order_headers_status_penerimaan');
            $table->index('kode_supplier', 'idx_order_headers_kode_supplier');
        });
        Schema::table('mutasi_requests', function (Blueprint $table) {
            $table->index('mutasi_header_id', 'idx_mutasi_requests_mutasi_header_id');
            $table->index('kode_mutasi', 'idx_mutasi_requests_kode_mutasi');
            $table->index('kode_barang', 'idx_mutasi_requests_kode_barang');
        });
        Schema::table('mutasi_headers', function (Blueprint $table) {
            $table->index('kode_mutasi', 'idx_mutasi_headers_kode_mutasi');
            $table->index('dari', 'idx_mutasi_headers_dari');
            $table->index('tujuan', 'idx_mutasi_headers_tujuan');
            $table->index('tgl_permintaan', 'idx_mutasi_headers_tgl_permintaan');
            $table->index('tgl_distribusi', 'idx_mutasi_headers_tgl_distribusi');
            $table->index('tgl_terima', 'idx_mutasi_headers_tgl_terima');
            $table->index('status', 'idx_mutasi_headers_status');
        });
        Schema::table('beban_rs', function (Blueprint $table) {
            $table->index('notransaksi', 'idx_beban_rs_notransaksi');
            $table->index('kode_beban', 'idx_beban_rs_kode_beban');
        });
        Schema::table('beban_hs', function (Blueprint $table) {
            $table->index('notransaksi', 'idx_beban_hs_notransaksi');
            $table->index('flag', 'idx_beban_hs_flag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beban_hs', function (Blueprint $table) {
            $table->dropIndex('idx_beban_hs_notransaksi');
            $table->dropIndex('idx_beban_hs_flag');
        });
        Schema::table('beban_rs', function (Blueprint $table) {
            $table->dropIndex('idx_beban_rs_notransaksi');
            $table->dropIndex('idx_beban_rs_kode_beban');
        });
        Schema::table('mutasi_headers', function (Blueprint $table) {
            $table->dropIndex('idx_mutasi_headers_kode_mutasi');
            $table->dropIndex('idx_mutasi_headers_dari');
            $table->dropIndex('idx_mutasi_headers_tujuan');
            $table->dropIndex('idx_mutasi_headers_tgl_permintaan');
            $table->dropIndex('idx_mutasi_headers_tgl_distribusi');
            $table->dropIndex('idx_mutasi_headers_tgl_terima');
            $table->dropIndex('idx_mutasi_headers_status');
        });
        Schema::table('mutasi_requests', function (Blueprint $table) {
            $table->dropIndex('idx_mutasi_requests_mutasi_header_id');
            $table->dropIndex('idx_mutasi_requests_kode_mutasi');
            $table->dropIndex('idx_mutasi_requests_kode_barang');
        });
        Schema::table('order_headers', function (Blueprint $table) {
            $table->dropIndex('idx_order_headers_nomor_order');
            $table->dropIndex('idx_order_headers_tgl_order');
            $table->dropIndex('idx_order_headers_kode_depo');
            $table->dropIndex('idx_order_headers_flag');
            $table->dropIndex('idx_order_headers_status_penerimaan');
            $table->dropIndex('idx_order_headers_kode_supplier');
        });
        Schema::table('order_records', function (Blueprint $table) {
            $table->dropIndex('idx_order_records_nomor_order');
            $table->dropIndex('idx_order_records_kode_barang');
        });
        Schema::table('pembayaran_hutang_rincis', function (Blueprint $table) {
            $table->dropIndex('idx_pembayaran_hutang_rincis_nopelunasan');
            $table->dropIndex('idx_pembayaran_hutang_rincis_noorder');
            $table->dropIndex('idx_pembayaran_hutang_rincis_nopenerimaan');
            $table->dropIndex('idx_pembayaran_hutang_rincis_nofaktur');
            $table->dropIndex('idx_pembayaran_hutang_rincis_kode_suplier');
        });
        Schema::table('pembayaran_hutangs', function (Blueprint $table) {
            $table->dropIndex('idx_pembayaran_hutangs_nopelunasan');
            $table->dropIndex('idx_pembayaran_hutangs_tgl_pelunasan');
            $table->dropIndex('idx_pembayaran_hutangs_flag');
            $table->dropIndex('idx_pembayaran_hutangs_flag_tgl');
        });
        Schema::table('pendapatanlain_h', function (Blueprint $table) {
            $table->dropIndex('idx_pendapatanlain_h_notrans');
            $table->dropIndex('idx_pendapatanlain_h_tgl');
            $table->dropIndex('idx_pendapatanlain_h_dari');
        });
        Schema::table('pendapatanlain_r', function (Blueprint $table) {
            $table->dropIndex('idx_pendapatanlain_r_notrans');
        });
        Schema::table('penerimaan_hs', function (Blueprint $table) {
            $table->dropIndex('idx_penerimaan_hs_nopenerimaan');
            $table->dropIndex('idx_penerimaan_hs_noorder');
            $table->dropIndex('idx_penerimaan_hs_tgl_penerimaan');
            $table->dropIndex('idx_penerimaan_hs_nofaktur');
            $table->dropIndex('idx_penerimaan_hs_tgl_faktur');
            $table->dropIndex('idx_penerimaan_hs_kode_suplier');
        });
        Schema::table('penerimaan_rs', function (Blueprint $table) {
            $table->dropIndex('idx_penerimaan_rs_nopenerimaan');
            $table->dropIndex('idx_penerimaan_rs_noorder');
            $table->dropIndex('idx_penerimaan_rs_kode_barang');
            $table->dropIndex('idx_penerimaan_rs_tgl_exprd');
        });
        Schema::table('penjualan_h_s', function (Blueprint $table) {
            $table->dropIndex('idx_penjualan_hs_nopenjualan');
            $table->dropIndex('idx_penjualan_hs_tgl_penjualan');
            $table->dropIndex('idx_penjualan_hs_kode_pelanggan');
            $table->dropIndex('idx_penjualan_hs_kode_dokter');
            $table->dropIndex('idx_penjualan_hs_kode_user');
            $table->dropIndex('idx_penjualan_hs_cara_bayar');
            $table->dropIndex('idx_penjualan_hs_flag');
            $table->dropIndex('idx_penjualan_hs_flag_setor');
        });
        Schema::table('penjualan_r_s', function (Blueprint $table) {
            $table->dropIndex('idx_penjualan_rs_nopenjualan');
            $table->dropIndex('idx_penjualan_rs_kode_barang');
        });
        Schema::table('penyesuaians', function (Blueprint $table) {
            $table->dropIndex('idx_penyesuaian_depo_tgl');
            $table->dropIndex('idx_penyesuaian_barang_depo_tgl');
        });
        Schema::table('retur_pembelian_hs', function (Blueprint $table) {
            $table->dropIndex('idx_retur_pembelian_hs_noretur');
            $table->dropIndex('idx_retur_pembelian_hs_tglretur');
            $table->dropIndex('idx_retur_pembelian_hs_kode_supplier');
            $table->dropIndex('idx_retur_pembelian_hs_flag');
        });
        Schema::table('stoks', function (Blueprint $table) {
            $table->dropIndex('idx_stoks_kodebarang_kodedepo');
        });
        Schema::table('stok_opnames', function (Blueprint $table) {
            $table->dropIndex('idx_stokop_barang_depo_tgl');
        });
        Schema::table('stok_opnames', function (Blueprint $table) {
            $table->dropIndex('idx_stok_opnames_tgl');
        });
        Schema::table('retur_penjualan_rs', function (Blueprint $table) {
            $table->dropIndex('idx_retur_rs_noretur');
            $table->dropIndex('idx_retur_rs_nopenjualan');
            $table->dropIndex('idx_retur_rs_kode_barang');
        });
        Schema::table('retur_penjualan_hs', function (Blueprint $table) {
            $table->dropIndex('idx_retur_hs_noretur');
            $table->dropIndex('idx_retur_hs_nopenjualan');
            $table->dropIndex('idx_retur_hs_tgl_retur');
            $table->dropIndex('idx_retur_hs_flag');
        });
        Schema::table('retur_pembelian_rs', function (Blueprint $table) {
            $table->dropIndex('idx_retur_pembelian_rs_noretur');
            $table->dropIndex('idx_retur_pembelian_rs_kode_barang');
        });
    }
};
