<?php

namespace App\Models\OldApp\Master;

use App\Helpers\GetStokFromEachyHelper;
use App\Models\OldApp\Transaktions\StokOpname;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Product extends Model
{
    use HasFactory;
    protected $connection = 'eachy';

    public function getStokAttribute()
    {
        $model = new StokOpname();
        $connection = $model->getConnectionName();

        $tglOpnameTerakhir = null;
        if (Schema::connection($connection)->hasTable($model->getTable())) {
            $tglOpnameTerakhir = StokOpname::select('tgl_opname')->orderBy('tgl_opname', 'desc')->first();
            if ($tglOpnameTerakhir) {
                $dataOpname = StokOpname::select('jumlah as qty')->where('kode_produk', $this->kode_produk)->where('tgl_opname', $tglOpnameTerakhir->tgl_opname)->first();
            }
        }

        $header = (object) array(
            // 'from' => $tglOpnameTerakhir->tgl_opname ?? date('Y-m-d'),
            'from' => date('Y-m-d'),
            'product_id' => $this->id,
            'kode_produk' => $this->kode_produk,
        );
        $singleDet = new GetStokFromEachyHelper;
        $stokMasuk = $singleDet->getSingleDetails($header, 'PEMBELIAN');
        $returPembelian = $singleDet->getSingleDetails($header, 'RETUR PEMBELIAN');
        $stokKeluar = $singleDet->getSingleDetails($header, 'PENJUALAN');
        $returPenjualan = $singleDet->getSingleDetails($header, 'RETUR PENJUALAN');
        $penyesuaian = $singleDet->getSingleDetails($header, 'FORM PENYESUAIAN');
        $distribusi = $singleDet->getSumSingleProduct($header);


        $masukBefore = collect($stokMasuk->before)->sum('qty') ?? 0;
        $masukPeriod = collect($stokMasuk->period)->sum('qty');
        $keluarBefore = collect($stokKeluar->before)->sum('qty') ?? 0;
        $keluarPeriod = collect($stokKeluar->period)->sum('qty');
        $retBeliBefore = collect($returPembelian->before)->sum('qty') ?? 0;
        $retBeliPeriod = collect($returPembelian->period)->sum('qty');
        $retJualBefore = collect($returPenjualan->before)->sum('qty') ?? 0;
        $retJualPeriod = collect($returPenjualan->period)->sum('qty');
        $penyeBefore = collect($penyesuaian->before)->sum('qty') ?? 0;
        $penyePeriod = collect($penyesuaian->period)->sum('qty');

        $distMB = collect($distribusi->masukbefore)->sum('qty') ?? 0;
        $distKB = collect($distribusi->keluarbefore)->sum('qty') ?? 0;
        $distMP = collect($distribusi->masukperiod)->sum('qty');
        $distKP = collect($distribusi->keluarperiod)->sum('qty');
        $stokAwal = 0;
        if (!$tglOpnameTerakhir) $stokAwal = $this->stok_awal;
        else $stokAwal = $dataOpname->qty ?? 0;
        $penyeM = $penyePeriod > 0 ? $penyePeriod : 0;
        $penyeK = $penyePeriod < 0 ? -$penyePeriod : 0;

        $sebelum = $masukBefore - $keluarBefore + $retJualBefore - $retBeliBefore + $penyeBefore + $distMB - $distKB;
        $berjalan = $masukPeriod - $keluarPeriod + $retJualPeriod - $retBeliPeriod + $penyePeriod + $distMP - $distKP;
        $masuk = $masukPeriod + $retJualPeriod  + $penyeM + $distMP;
        $keluar = $keluarPeriod +  $retBeliPeriod + $penyeK +  $distKP;
        $awal = $stokAwal + $sebelum;
        $sekarang = $awal + $berjalan;
        // $sekarang = 0;
        // $produk->stok_awal = $awal;
        // $produk->stokSekarang = $sekarang;
        // $produk->stokBerjalan = $berjalan;


        return $this->attributes['stok'] = $sekarang;
        // return $this->attributes['stok'] = [
        //     'sekarang' => $sekarang,
        //     'masuk' => $masuk,
        //     'keluar' => $keluar
        // ];
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class); // kategori_id yang ada di tabel produk itu milik tabel kategori
    }

    public function rak()
    {
        return $this->belongsTo(Rak::class);
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class);
    }
    public function satuanBesar()
    {
        return $this->belongsTo(SatuanBesar::class);
    }

    public function merk()
    {
        return $this->belongsTo(Merk::class);
    }
}
