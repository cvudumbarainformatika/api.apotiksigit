<?php

namespace App\Helpers;

use App\Models\OldApp\Master\Info;
use App\Models\OldApp\Transaktions\DistribusiAntarToko;
use App\Models\OldApp\Transaktions\StokOpname;
use App\Models\OldApp\Transaktions\Transaction;
use Illuminate\Support\Facades\Schema;

class GetStokFromEachyHelper
{
  public function getSingleDetails($header, $nama)
  {
    $dataOpname = [];
    $tglOpnameTerakhir = null;
    if (Schema::connection('eachy')->hasTable('stok_opnames')) {
      $tglOpnameTerakhir = StokOpname::select('tgl_opname')
        ->whereDate('tgl_opname', '<', $header->from)
        ->orderBy('tgl_opname', 'desc')->first();
      if ($tglOpnameTerakhir) {
        $dataOpname = StokOpname::select('jumlah as qty')->where('kode_produk', $header->kode_produk)->where('tgl_opname', $tglOpnameTerakhir->tgl_opname)->get();
      }
    }
    if (sizeof($dataOpname)  > 0) {
      // $before = $dataOpname;
      // $before = null;
      $before = Transaction::select(
        'detail_transactions.qty'
      )->leftJoin('detail_transactions', 'detail_transactions.transaction_id', '=', 'transactions.id')
        ->where('detail_transactions.product_id', $header->product_id)
        ->where('transactions.nama', '=', $nama)
        ->where('transactions.status', '>=', 2)
        ->whereDate('transactions.tanggal', '<', $header->from)
        ->whereDate('transactions.tanggal', '>', $tglOpnameTerakhir->tgl_opname)
        ->get();
    } else {
      $before = Transaction::select(
        'detail_transactions.qty'
      )->leftJoin('detail_transactions', 'detail_transactions.transaction_id', '=', 'transactions.id')
        ->where('detail_transactions.product_id', $header->product_id)
        ->where('transactions.nama', '=', $nama)
        ->where('transactions.status', '>=', 2)
        ->whereDate('transactions.tanggal', '<', $header->from)
        ->get();
    }

    $period = Transaction::select(
      'detail_transactions.qty'
    )->leftJoin('detail_transactions', 'detail_transactions.transaction_id', '=', 'transactions.id')
      ->where('detail_transactions.product_id', $header->product_id)
      ->where('transactions.nama', '=', $nama)
      ->where('transactions.status', '>=', 2)
      ->whereDate('transactions.tanggal', '>=', $header->from)->get(); // period is today
    $data = (object) array(
      'before' => $before,
      'period' => $period,
    );
    return $data;
  }
  public function getSumSingleProduct($header)
  {
    // before itu diantara tgl opname dan request->from
    // maka query tgl stok opnam adalah yg tanggal nya < request->from
    $me = Info::first();
    $dataOpname = [];
    if (Schema::connection('eachy')->hasTable('stok_opnames')) {
      $tglOpnameTerakhir = StokOpname::select('tgl_opname')
        ->whereDate('tgl_opname', '<', $header->from)
        ->orderBy('tgl_opname', 'desc')->first();
      if ($tglOpnameTerakhir) {
        $dataOpname = StokOpname::select('jumlah as qty')->where('kode_produk', $header->kode_produk)->where('tgl_opname', $tglOpnameTerakhir->tgl_opname)->get();
      }
    }
    if (sizeof($dataOpname) > 0) {
      $masukbefore = DistribusiAntarToko::select(
        'distribusi_antar_tokos.qty'
      )
        ->leftJoin('header_distribusis', 'header_distribusis.nodistribusi', '=', 'distribusi_antar_tokos.nodistribusi')
        ->where('distribusi_antar_tokos.kode_produk', $header->kode_produk)
        ->where('header_distribusis.tujuan', $me->kodecabang)
        ->whereDate('header_distribusis.tgl_terima', '<', $header->from)
        ->whereDate('header_distribusis.tgl_terima', '>', $tglOpnameTerakhir->tgl_opname)
        ->get();

      $keluarbefore = DistribusiAntarToko::select(
        'distribusi_antar_tokos.qty'
      )
        ->leftJoin('header_distribusis', 'header_distribusis.nodistribusi', '=', 'distribusi_antar_tokos.nodistribusi')
        ->where('distribusi_antar_tokos.kode_produk', $header->kode_produk)
        ->where('header_distribusis.dari', $me->kodecabang)
        ->whereDate('header_distribusis.tgl_distribusi', '<', $header->from)
        ->whereDate('header_distribusis.tgl_distribusi', '>', $tglOpnameTerakhir->tgl_opname)
        ->get();
    } else {
      $masukbefore = DistribusiAntarToko::select(
        'distribusi_antar_tokos.qty'
      )
        ->leftJoin('header_distribusis', 'header_distribusis.nodistribusi', '=', 'distribusi_antar_tokos.nodistribusi')
        ->where('distribusi_antar_tokos.kode_produk', $header->kode_produk)
        ->where('header_distribusis.tujuan', $me->kodecabang)
        ->whereDate('header_distribusis.tgl_terima', '<', $header->from)
        ->get();

      $keluarbefore = DistribusiAntarToko::select(
        'distribusi_antar_tokos.qty'
      )
        ->leftJoin('header_distribusis', 'header_distribusis.nodistribusi', '=', 'distribusi_antar_tokos.nodistribusi')
        ->where('distribusi_antar_tokos.kode_produk', $header->kode_produk)
        ->where('header_distribusis.dari', $me->kodecabang)
        ->whereDate('header_distribusis.tgl_distribusi', '<', $header->from)
        ->get();
    }
    $from = date('Y-m-d', strtotime($header->from));
    $masukperiod = DistribusiAntarToko::select(
      'distribusi_antar_tokos.qty'
    )
      ->leftJoin('header_distribusis', 'header_distribusis.nodistribusi', '=', 'distribusi_antar_tokos.nodistribusi')
      ->where('distribusi_antar_tokos.kode_produk', $header->kode_produk)
      ->where('header_distribusis.tujuan', $me->kodecabang)
      ->whereDate('header_distribusis.tgl_terima', '>=', $from)
      ->get();
    $keluarperiod = DistribusiAntarToko::select(
      'distribusi_antar_tokos.qty'
    )
      ->leftJoin('header_distribusis', 'header_distribusis.nodistribusi', '=', 'distribusi_antar_tokos.nodistribusi')
      ->where('distribusi_antar_tokos.kode_produk', $header->kode_produk)
      ->where('header_distribusis.dari', $me->kodecabang)
      ->whereDate('header_distribusis.tgl_distribusi', '>=', $from) // period is today
      ->get();
    $data = (object) array(
      // 'me' => $me->kodecabang,
      'masukbefore' => $masukbefore,
      'masukperiod' => $masukperiod,
      'keluarbefore' => $keluarbefore,
      'keluarperiod' => $keluarperiod,
    );
    return $data;
  }
}
