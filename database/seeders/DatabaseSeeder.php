<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Setting\Menu;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $sa = User::where('username', '=', 'sa')->first();
        if (!$sa) {
            User::create([
                'kode' => 'USR000000',
                'username' => 'sa',
                'nama' => 'Super Admin',
                'password' => bcrypt('sasa0102'),
                'email' => 'sa@app.com',
                'kode_jabatan' => 'root',

            ]);
        }
        // User::factory(5)->create();
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $conter = DB::table('counter')->first();
        if (!$conter) {
            DB::table('counter')->insert([
                'kode_barang' => 0,
                'kode_pelanggan' => 0,
                'kode_satuan' => 0,
                'kode_supplier' => 0,
                'kode_jabatan' => 0
            ]);
        }
        // seeder menu
        // dahsboard
        $dashoard = Menu::firstOrCreate(
            ['title' => 'Dashboard'],
            [
                'icon' => 'home',
                'url' => 'admin',
                'name' => 'dashboard',
                'view' => '/views/dashboard',
                'component' => 'IndexPage',
            ]
        );
        //  MASTER
        $master = Menu::firstOrCreate(
            ['title' => 'Master'],
            [
                'icon' => 'layers',
                'url' => 'admin/master',
                'name' => null,
                'view' => null,
                'component' => null,
            ]
        );
        // children
        $master->children()->updateOrCreate(
            [
                'title' => 'Master Satuan',
            ],
            [
                'icon' => 'tag',
                'url' => 'admin/master/satuan',
                'name' => 'master.satuan',
                'view' => '/views/master/satuan',
                'component' => 'IndexPage',
            ]
        );
        $master->children()->updateOrCreate(
            [
                'title' => 'Master Merk',
            ],
            [
                'icon' => 'tag',
                'url' => 'admin/master/merk',
                'name' => 'master.merk',
                'view' => '/views/master/merk',
                'component' => 'IndexPage',
            ]
        );
        $master->children()->updateOrCreate(
            [
                'title' => 'Master Rak',
            ],
            [
                'icon' => 'tag',
                'url' => 'admin/master/rak',
                'name' => 'master.rak',
                'view' => '/views/master/rak',
                'component' => 'IndexPage',
            ]
        );
        
        $master->children()->updateOrCreate(
            [
                'title' => 'Master Kategori',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/master/kategori',
                'name' => 'master.kategori',
                'view' => '/views/master/kategori',
                'component' => 'IndexPage',
            ]
        );
        $master->children()->updateOrCreate(
            [
                'title' => 'Master Barang',
            ],
            [
                'icon' => 'tag',
                'url' => 'admin/master/barang',
                'name' => 'master.barang',
                'view' => '/views/master/barang',
                'component' => 'IndexPage',
            ]
        );
        $master->children()->updateOrCreate(
            [
                'title' => 'Master Jabatan',
            ],
            [
                'icon' => 'network',
                'url' => 'admin/master/jabatan',
                'name' => 'master.jabatan',
                'view' => '/views/master/jabatan',
                'component' => 'IndexPage',
            ]
        );
        $master->children()->updateOrCreate(
            [
                'title' => 'Master Supplier',
            ],
            [
                'icon' => 'users-round',
                'url' => 'admin/master/supplier',
                'name' => 'master.supplier',
                'view' => '/views/master/supplier',
                'component' => 'IndexPage',
            ]
        );
        $master->children()->updateOrCreate(
            [
                'title' => 'Master Pelanggan',
            ],
            [
                'icon' => 'users',
                'url' => 'admin/master/pelanggan',
                'name' => 'master.pelanggan',
                'view' => '/views/master/pelanggan',
                'component' => 'IndexPage',
            ]
        );
        $master->children()->updateOrCreate(
            [
                'title' => 'Master Pengguna',
            ],
            [
                'icon' => 'users',
                'url' => 'admin/master/user',
                'name' => 'master.user',
                'view' => '/views/master/user',
                'component' => 'IndexPage',
            ]
        );
        $master->children()->updateOrCreate(
            [
                'title' => 'Master Dokter',
            ],
            [
                'icon' => 'users',
                'url' => 'admin/master/dokter',
                'name' => 'master.dokter',
                'view' => '/views/master/dokter',
                'component' => 'IndexPage',
            ]
        );
        $master->children()->updateOrCreate(
            [
                'title' => 'Master Beban',
            ],
            [
                'icon' => 'users',
                'url' => 'admin/master/beban',
                'name' => 'master.beban',
                'view' => '/views/master/beban',
                'component' => 'IndexPage',
            ]
        );
        // GUDANG
        $gudang = Menu::firstOrCreate(
            ['title' => 'Gudang'],
            [
                'icon' => 'layers',
                'url' => 'admin/gudang',
                'name' => null,
                'view' => null,
                'component' => null,
            ]
        );
        $gudang->children()->updateOrCreate(
            [
                'title' => 'Order Product',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/gudang/order',
                'name' => 'gudang.order',
                'view' => '/views/gudang/order',
                'component' => 'IndexPage',
            ]
        );
        $gudang->children()->updateOrCreate(
            [
                'title' => 'Penerimaan',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/gudang/penerimaan',
                'name' => 'gudang.penerimaan',
                'view' => '/views/gudang/penerimaan',
                'component' => 'IndexPage',
            ]
        );

        //  TRANSAKSI
        $transaksi = Menu::firstOrCreate(
            ['title' => 'Transaksi'],
            [
                'icon' => 'layers',
                'url' => 'admin/transaksi',
                'name' => null,
                'view' => null,
                'component' => null,
            ]
        );
        
        // children
        $transaksi->children()->updateOrCreate(
            [
                'title' => 'Permintaan',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/transaksi/permintaan',
                'name' => 'transaksi.permintaan',
                'view' => '/views/transaksi/permintaan',
                'component' => 'IndexPage',
            ]
        ); 
        $transaksi->children()->updateOrCreate(
            [
                'title' => 'Distribusi',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/transaksi/distribusi',
                'name' => 'transaksi.distribusi',
                'view' => '/views/transaksi/distribusi',
                'component' => 'IndexPage',
            ]
        );
         $transaksi->children()->updateOrCreate(
            [
                'title' => 'Penerimaan Depo',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/transaksi/penerimaandepo',
                'name' => 'transaksi.penerimaandepo',
                'view' => '/views/transaksi/penerimaandepo',
                'component' => 'IndexPage',
            ]
        );
       
        $transaksi->children()->updateOrCreate(
            [
                'title' => 'Penjualan',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/transaksi/penjualan',
                'name' => 'transaksi.penjualan',
                'view' => '/views/transaksi/penjualan',
                'component' => 'IndexPage',
            ]
        );
        $transaksi->children()->updateOrCreate(
            [
                'title' => 'Retur Penjualan',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/transaksi/returpenjualan',
                'name' => 'transaksi.returpenjualan',
                'view' => '/views/transaksi/returpenjualan',
                'component' => 'IndexPage',
            ]
        );
        $transaksi->children()->updateOrCreate(
            [
                'title' => 'Stock List',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/transaksi/stock',
                'name' => 'transaksi.stock',
                'view' => '/views/transaksi/stock',
                'component' => 'IndexPage',
            ]
        );
        $transaksi->children()->updateOrCreate(
            [
                'title' => 'Retur PBF',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/transaksi/returpembelian',
                'name' => 'transaksi.returpembelian',
                'view' => '/views/transaksi/returpembelian',
                'component' => 'IndexPage',
            ]
        );
        $transaksi->children()->updateOrCreate(
            [
                'title' => 'Beban Pengeluaran',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/transaksi/beban',
                'name' => 'transaksi.beban',
                'view' => '/views/transaksi/beban',
                'component' => 'IndexPage',
            ]
        );
        $transaksi->children()->updateOrCreate(
            [
                'title' => 'Pendapatan Lain',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/transaksi/pendapatanlain',
                'name' => 'transaksi.pendapatanlain',
                'view' => '/views/transaksi/pendapatanlain',
                'component' => 'IndexPage',
            ]
        );
        $transaksi->children()->updateOrCreate(
            [
                'title' => 'Pembayaran Hutang',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/transaksi/pembyhutang',
                'name' => 'transaksi.pembyhutang',
                'view' => '/views/transaksi/pembayaranhutang',
                'component' => 'IndexPage',
            ]
        );
        //  LAPORAN
        $laporan = Menu::firstOrCreate(
            ['title' => 'Laporan'],
            [
                'icon' => 'layers',
                'url' => 'admin/laporan',
                'name' => null,
                'view' => null,
                'component' => null,
            ]
        );
        $laporan->children()->updateOrCreate(
            [
                'title' => 'Kartu Stok',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/laporan/kartustok',
                'name' => 'laporan.kartustok',
                'view' => '/views/laporan/kartustok',
                'component' => 'IndexPage',
            ]
        );
        $laporan->children()->updateOrCreate(
            [
                'title' => 'Laporan Penjualan',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/laporan/penjualan',
                'name' => 'laporan.penjualan',
                'view' => '/views/laporan/penjualan',
                'component' => 'IndexPage',
            ]
        );
        $laporan->children()->updateOrCreate(
            [
                'title' => 'Laporan Pembelian',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/laporan/pembelian',
                'name' => 'laporan.pembelian',
                'view' => '/views/laporan/pembelian',
                'component' => 'IndexPage',
            ]
        );
        $laporan->children()->updateOrCreate(
            [
                'title' => 'Laporan Labarugi',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/laporan/labarugi',
                'name' => 'laporan.labarugi',
                'view' => '/views/laporan/labarugi',
                'component' => 'IndexPage',
            ]
        );
        $laporan->children()->updateOrCreate(
            [
                'title' => 'Laporan Hutang',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/laporan/hutang',
                'name' => 'laporan.hutang',
                'view' => '/views/laporan/hutang',
                'component' => 'IndexPage',
            ]
        );
        $laporan->children()->updateOrCreate(
            [
                'title' => 'Laporan Fast Moving',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/laporan/fastmoving',
                'name' => 'laporan.fastmoving',
                'view' => '/views/laporan/fastmoving',
                'component' => 'IndexPage',
            ]
        );
        //  Setting
        $setting = Menu::firstOrCreate(
            ['title' => 'Settings'],
            [
                'icon' => 'layers',
                'url' => 'admin/setting',
                'name' => null,
                'view' => null,
                'component' => null,
            ]
        );
        $setting->children()->updateOrCreate(
            [
                'title' => 'Aplikasi',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/settings/aplikasi',
                'name' => 'settings.aplikasi',
                'view' => '/views/settings/aplikasi',
                'component' => 'IndexPage',
            ]
        );
        $setting->children()->updateOrCreate(
            [
                'title' => 'Hak Akses',
            ],
            [
                'icon' => 'layers',
                'url' => 'admin/settings/hak-akses',
                'name' => 'settings.hakakses',
                'view' => '/views/settings/hakakses',
                'component' => 'IndexPage',
            ]
        );
    }
}
