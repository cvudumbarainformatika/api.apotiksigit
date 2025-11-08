<?php

namespace App\Http\Controllers\Api\Laporan;

use App\Http\Controllers\Controller;
use App\Models\Transactions\PenjualanR;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DasboardController extends Controller
{
    public function fasmoving()
    {
        $req = [
            'from' => date('Y-m-01 00:00:00'),
            'to' => date('Y-m-d H:i:s'),
        ];

        $data = DB::table('penjualan_r_s')
            ->select(
                // 'barangs.kode',
                'barangs.nama',
                // DB::raw('GROUP_CONCAT(DISTINCT penjualan_r_s.satuan_k ORDER BY penjualan_r_s.satuan_k SEPARATOR ", ") AS satuan_list'),
                // DB::raw('SUM(penjualan_r_s.jumlah_k) AS jumlah_penjualan'),
                // DB::raw('IFNULL(SUM(retur_penjualan_rs.jumlah_k), 0) AS jumlah_retur'),
                DB::raw('(SUM(penjualan_r_s.jumlah_k) - IFNULL(SUM(retur_penjualan_rs.jumlah_k), 0)) AS total_penjualan'),
                // DB::raw('SUM(penjualan_r_s.subtotal) AS total_harga_jual'),
                // DB::raw('IFNULL((SUM(retur_penjualan_rs.jumlah_k * retur_penjualan_rs.harga)-SUM(retur_penjualan_rs.diskon)), 0) AS total_harga_retur'),
                //DB::raw('round(SUM(penjualan_r_s.subtotal) - IFNULL((SUM(retur_penjualan_rs.jumlah_k * retur_penjualan_rs.harga)-SUM(retur_penjualan_rs.diskon)), 0)) AS total_harga_bersih',2)
            )
            ->leftJoin('penjualan_h_s', 'penjualan_h_s.nopenjualan', '=', 'penjualan_r_s.nopenjualan')
            ->leftJoin('retur_penjualan_rs', 'retur_penjualan_rs.nopenjualan', '=', 'penjualan_r_s.nopenjualan')
            ->leftJoin('barangs', 'barangs.kode', '=', 'penjualan_r_s.kode_barang')
            ->whereBetween('penjualan_h_s.tgl_penjualan', [
                $req['from'] . ' 00:00:00',
                $req['to'] . ' 23:59:59'
            ])
            // ->when(request('q'), function ($q) {
            //     $q->where('barangs.nama', 'like', '%' . request('q') . '%')
            //     ->orWhere('barangs.kode', 'like', '%' . request('q') . '%');
            // })
            ->groupBy('penjualan_r_s.kode_barang', 'barangs.kode', 'barangs.nama')
            ->orderByRaw('(SUM(penjualan_r_s.jumlah_k) - IFNULL(SUM(retur_penjualan_rs.jumlah_k), 0)) DESC')
            ->limit(5)
            ->get();

        return new JsonResponse([
            'data' => $data
        ]);
    }

    public function toppbf()
    {
        $req = [
            'from' => date('Y-m-01 00:00:00'),
            'to' => date('Y-m-d H:i:s'),
        ];

        $data = DB::table('penerimaan_hs')
            ->select(
                'suppliers.nama',
                DB::raw('count(penerimaan_hs.kode_suplier) AS jumlah'),
            )
            ->leftJoin('suppliers', 'suppliers.kode', '=', 'penerimaan_hs.kode_suplier')
            ->whereBetween('penerimaan_hs.tgl_penerimaan', [
                $req['from'] . ' 00:00:00',
                $req['to'] . ' 23:59:59'
            ])
            ->groupBy('penerimaan_hs.kode_suplier', 'suppliers.nama')
            ->orderByRaw('count(penerimaan_hs.kode_suplier) DESC')
            ->limit(5)
            ->get();

        return new JsonResponse([
            'data' => $data
        ]);
    }
    public function penjualanPembelianPerbulanTahunIni()
    {
        $tahunIni = Carbon::now()->year;

        // Ambil data real dari database
        $dataDB = DB::table('penjualan_r_s as r')
            ->join('penjualan_h_s as h', 'h.nopenjualan', '=', 'r.nopenjualan')
            ->selectRaw('MONTH(h.tgl_penjualan) as bulan, SUM(r.subtotal) as total')
            ->whereYear('h.tgl_penjualan', $tahunIni)
            ->groupBy(DB::raw('MONTH(h.tgl_penjualan)'))
            ->get();

        // Buat struktur bulan default
        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Des'];
        $bulanData = array_fill(0, 12, 0); // isi 12 elemen dengan 0

        // Isi data sesuai hasil query
        foreach ($dataDB as $row) {
            $index = $row->bulan - 1; // karena index array mulai dari 0
            $bulanData[$index] = (float) $row->total;
        }
        // Ambil total penerimaan per bulan di tahun berjalan
        $dataDBPem = DB::table('penerimaan_rs as r')
            ->join('penerimaan_hs as h', 'h.nopenerimaan', '=', 'r.nopenerimaan')
            ->selectRaw('MONTH(h.tgl_penerimaan) as bulan, SUM(r.subtotal) as total')
            ->whereYear('h.tgl_penerimaan', $tahunIni)
            ->groupBy(DB::raw('MONTH(h.tgl_penerimaan)'))
            ->get();
        $bulanDataPem = array_fill(0, 12, 0); // isi 12 elemen dengan 0
        foreach ($dataDBPem as $row) {
            $index = $row->bulan - 1; // karena index array mulai dari 0
            $bulanDataPem[$index] = (float) $row->total;
        }
        $data['data'] = [
            'bulan' => $bulanLabels,
            'penjualan' => $bulanData,
            'pembelian' => $bulanDataPem,
        ];

        return new JsonResponse($data);
    }
    public function penjualanPembelianHarian()
    {
        $penj = $this->laporanPenjualanHarian();
        $pem = $this->laporanPenerimaanHarian();
        return new JsonResponse([
            'penjualan' => $penj,
            'pembelian' => $pem,
        ]);
    }
    private function laporanPenjualanHarian()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // --- 1️⃣ TOTAL PENJUALAN (dari detail subtotal)
        $totalHariIni = DB::table('penjualan_r_s as r')
            ->join('penjualan_h_s as h', 'h.nopenjualan', '=', 'r.nopenjualan')
            ->whereDate('h.tgl_penjualan', $today)
            ->sum('r.subtotal');

        $totalKemarin = DB::table('penjualan_r_s as r')
            ->join('penjualan_h_s as h', 'h.nopenjualan', '=', 'r.nopenjualan')
            ->whereDate('h.tgl_penjualan', $yesterday)
            ->sum('r.subtotal');

        // hitung perubahan total penjualan
        [$statusTotal, $persenTotal] = $this->hitungPerubahan($totalHariIni, $totalKemarin);

        // --- 2️⃣ JUMLAH TRANSAKSI (dari header)
        $transaksiHariIni = DB::table('penjualan_h_s')
            ->whereDate('tgl_penjualan', $today)
            ->count();

        $transaksiKemarin = DB::table('penjualan_h_s')
            ->whereDate('tgl_penjualan', $yesterday)
            ->count();

        // hitung perubahan jumlah transaksi
        [$statusTransaksi, $persenTransaksi] = $this->hitungPerubahan($transaksiHariIni, $transaksiKemarin);

        // --- 3️⃣ Hasil akhir
        $result = [
            'total_penjualan' => [
                'tanggal' => $today->toDateString(),
                'hari_ini' => (float) $totalHariIni,
                'kemarin' => (float) $totalKemarin,
                'persentase_perubahan' => $persenTotal,
                'status' => $statusTotal,
            ],
            'jumlah_transaksi' => [
                'tanggal' => $today->toDateString(),
                'hari_ini' => $transaksiHariIni,
                'kemarin' => $transaksiKemarin,
                'persentase_perubahan' => $persenTransaksi,
                'status' => $statusTransaksi,
            ],
        ];

        return $result;
    }
    private function laporanPenerimaanHarian()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // --- 1️⃣ TOTAL NOMINAL PENERIMAAN
        $totalHariIni = DB::table('penerimaan_rs as r')
            ->join('penerimaan_hs as h', 'h.nopenerimaan', '=', 'r.nopenerimaan')
            ->whereDate('h.tgl_penerimaan', $today)
            ->sum('r.subtotal');

        $totalKemarin = DB::table('penerimaan_rs as r')
            ->join('penerimaan_hs as h', 'h.nopenerimaan', '=', 'r.nopenerimaan')
            ->whereDate('h.tgl_penerimaan', $yesterday)
            ->sum('r.subtotal');

        [$statusTotal, $persenTotal] = $this->hitungPerubahan($totalHariIni, $totalKemarin);

        // --- 2️⃣ JUMLAH TRANSAKSI PENERIMAAN
        $transaksiHariIni = DB::table('penerimaan_hs')
            ->whereDate('tgl_penerimaan', $today)
            ->count();

        $transaksiKemarin = DB::table('penerimaan_hs')
            ->whereDate('tgl_penerimaan', $yesterday)
            ->count();

        [$statusTransaksi, $persenTransaksi] = $this->hitungPerubahan($transaksiHariIni, $transaksiKemarin);

        // --- 3️⃣ HASIL AKHIR
        $result = [
            'total_penerimaan' => [
                'tanggal' => $today->toDateString(),
                'hari_ini' => (float) $totalHariIni,
                'kemarin' => (float) $totalKemarin,
                'persentase_perubahan' => $persenTotal,
                'status' => $statusTotal,
            ],
            'jumlah_transaksi' => [
                'tanggal' => $today->toDateString(),
                'hari_ini' => $transaksiHariIni,
                'kemarin' => $transaksiKemarin,
                'persentase_perubahan' => $persenTransaksi,
                'status' => $statusTransaksi,
            ],
        ];

        return $result;
    }
    /**
     * Helper untuk hitung perubahan dan status
     */
    private function hitungPerubahan($hariIni, $kemarin)
    {
        $status = 'tetap';
        $persentase = 0;

        if ($kemarin > 0) {
            $selisih = $hariIni - $kemarin;
            $persentase = ($selisih / $kemarin) * 100;

            if ($selisih > 0) {
                $status = 'naik';
            } elseif ($selisih < 0) {
                $status = 'turun';
            }
        } elseif ($hariIni > 0 && $kemarin == 0) {
            $status = 'naik';
            $persentase = 100;
        }

        return [$status, round($persentase, 2)];
    }
}
