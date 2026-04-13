<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransaksiController extends Controller
{
    // ===============================
    // HALAMAN MASUK
    // ===============================
    public function masukPage()
    {
        $area = DB::table('t_area')->orderBy('id')->get();

        $kendaraanTersedia = DB::table('t_kendaraan as k')
            ->join('t_tarif as t', 'k.id_tarif', '=', 't.id')
            ->whereNull('k.status')
            ->select('k.id', 'k.plat_kendaraan', 't.jenis_kendaraan')
            ->get();

        $transaksi = DB::table('t_transaksi as tr')
            ->join('t_kendaraan as k', 'tr.id_kendaraan', '=', 'k.id')
            ->join('t_tarif as t', 'tr.id_tarif', '=', 't.id')
            ->join('t_area as a', 'tr.id_area', '=', 'a.id')
            ->where('tr.status', 'parkir')
            ->select(
                'tr.*',
                'k.plat_kendaraan',
                't.jenis_kendaraan',
                't.tarif_per_jam',
                'a.nama_area'
            )
            ->orderBy('tr.id', 'desc')
            ->get();

        $terisiPerArea = DB::table('t_transaksi')
            ->select('id_area', DB::raw('COUNT(*) as terisi'))
            ->where('status', 'parkir')
            ->groupBy('id_area')
            ->pluck('terisi', 'id_area');

        return view('Transaksi.masuk', compact(
            'area',
            'kendaraanTersedia',
            'transaksi',
            'terisiPerArea'
        ));
    }

    // ===============================
    // HALAMAN KELUAR
    // ===============================
    public function keluarPage()
    {
        $transaksi = DB::table('t_transaksi as tr')
            ->join('t_kendaraan as k', 'tr.id_kendaraan', '=', 'k.id')
            ->join('t_tarif as t', 'tr.id_tarif', '=', 't.id')
            ->join('t_area as a', 'tr.id_area', '=', 'a.id')
            ->where('tr.status', 'keluar')
            ->select(
                'tr.*',
                'k.plat_kendaraan',
                't.jenis_kendaraan',
                'a.nama_area'
            )
            ->orderBy('tr.id', 'desc')
            ->get();

        return view('Transaksi.keluar', compact('transaksi'));
    }

    // ===============================
    // SIMPAN MASUK
    // ===============================
    public function masuk(Request $request)
    {
        $request->validate([
            'id_kendaraan' => 'required',
            'id_area' => 'required'
        ]);

        $cek = DB::table('t_transaksi')
            ->where('id_kendaraan', $request->id_kendaraan)
            ->where('status', 'parkir')
            ->exists();

        if ($cek) {
            return back()->with('error', 'Kendaraan masih parkir!');
        }

        $kendaraan = DB::table('t_kendaraan')
            ->where('id', $request->id_kendaraan)
            ->first();

        if (!$kendaraan) {
            return back()->with('error', 'Kendaraan tidak ditemukan');
        }

        DB::table('t_kendaraan')
            ->where('id', $kendaraan->id)
            ->update(['status' => 'parkir']);

        DB::table('t_transaksi')->insert([
            'id_kendaraan' => $kendaraan->id,
            'id_tarif' => $kendaraan->id_tarif,
            'waktu_masuk' => now(),
            'status' => 'parkir',
            'id_user' => Auth::id(),
            'id_area' => $request->id_area
        ]);

        DB::table('t_log_aktivitas')->insert([
            'id_user' => Auth::id(),
            'aktivitas' => "Masuk: {$kendaraan->plat_kendaraan}",
            'waktu_aktivitas' => now()
        ]);

        return back()->with('success', 'Kendaraan berhasil masuk');
    }

    // ===============================
    // KELUAR
    // ===============================
    public function keluar($id)
    {
        $transaksi = DB::table('t_transaksi')->where('id', $id)->first();

        if (!$transaksi) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        $tarif = DB::table('t_tarif')->where('id', $transaksi->id_tarif)->first();

        $waktuKeluar = now();
        $selisih = strtotime($waktuKeluar) - strtotime($transaksi->waktu_masuk);

        $totalMenit = ceil($selisih / 60);
        $jam = floor($totalMenit / 60);
        $menit = $totalMenit % 60;

        $durasi = "{$jam} jam {$menit} menit";
        $biaya = round($totalMenit * ($tarif->tarif_per_jam / 60));

        DB::table('t_transaksi')->where('id', $id)->update([
            'waktu_keluar' => $waktuKeluar,
            'durasi_jam' => $jam,
            'durasi_menit' => $menit,
            'durasi' => $durasi,
            'status' => 'keluar',
            'biaya_total' => $biaya
        ]);

        $kendaraan = DB::table('t_kendaraan')
            ->where('id', $transaksi->id_kendaraan)
            ->first();

        DB::table('t_log_aktivitas')->insert([
            'id_user' => Auth::id(),
            'aktivitas' => "Keluar: {$kendaraan->plat_kendaraan}",
            'waktu_aktivitas' => now()
        ]);

        return response()->json([
            'success' => true,
            'biaya_total' => $biaya,
            'durasi' => $durasi
        ]);
    }

    // ===============================
    // BAYAR CASH
    // ===============================
    public function bayarCash(Request $request, $id)
    {
        $transaksi = DB::table('t_transaksi')->where('id', $id)->first();

        if (!$transaksi) {
            return response()->json(['error' => 'Data tidak ditemukan'], 404);
        }

        if ($transaksi->status !== 'keluar') {
            return response()->json(['error' => 'Belum bisa dibayar'], 400);
        }

        if ($request->uang_dibayar < $transaksi->biaya_total) {
            return response()->json(['error' => 'Uang kurang'], 400);
        }

        $kembalian = $request->uang_dibayar - $transaksi->biaya_total;

        DB::table('t_kendaraan')
            ->where('id', $transaksi->id_kendaraan)
            ->update(['status' => 'selesai']);

        $kendaraan = DB::table('t_kendaraan')->where('id', $transaksi->id_kendaraan)->first();
        $tarif = DB::table('t_tarif')->where('id', $transaksi->id_tarif)->first();
        $area = DB::table('t_area')->where('id', $transaksi->id_area)->first();

        DB::table('t_riwayat')->insert([
            'id_transaksi' => $transaksi->id,
            'plat_kendaraan' => $kendaraan->plat_kendaraan,
            'jenis_kendaraan' => $tarif->jenis_kendaraan,
            'nama_area' => $area->nama_area,
            'waktu_masuk' => $transaksi->waktu_masuk,
            'waktu_keluar' => $transaksi->waktu_keluar,
            'durasi' => $transaksi->durasi,
            'biaya_total' => $transaksi->biaya_total,
            'uang_dibayar' => $request->uang_dibayar,
            'kembalian' => $kembalian,
            'id_user' => $transaksi->id_user,
            'status_pembayaran' => 'lunas',
            'metode_pembayaran' => 'cash',
            'created_at' => now()
        ]);

        DB::table('t_transaksi')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'kembalian' => $kembalian
        ]);
    }

    // ===============================
    // STRUK
    // ===============================
    public function struk($id)
    {
        $data = DB::table('t_riwayat')
            ->where('id_transaksi', $id)
            ->first();

        if (!$data) {
            return "Data tidak ditemukan";
        }

        return view('Transaksi.struk', compact('data'));
    }
}