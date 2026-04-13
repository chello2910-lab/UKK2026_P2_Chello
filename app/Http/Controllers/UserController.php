<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        // 🔥 update status petugas setiap load halaman
        $this->updateStatusShiftPetugas();

        $admin = User::where('role', 'admin')->get();
        $petugas = User::where('role', 'petugas')->get();
        $owner = User::where('role', 'owner')->get();

        // 🔥 FIX: tambahin users biar tidak error di layout
        return view('User.user', [
            'admin' => $admin,
            'petugas' => $petugas,
            'owner' => $owner,
            'users' => collect() // 🔥 penting!
        ]);
    }

    public function create()
    {
        $user = null;
        return view('User.create', compact('user'));
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('User.create', compact('user'));
    }

    public function delete($id)
    {
        $user = User::find($id);

        if ($user) {
            $nama = $user->name;
            $user->delete();

            $this->logAktivitas('Hapus user: ' . $nama);
        }

        return redirect()->route('user')->with('success', 'User berhasil dihapus');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required',
            'shift' => 'required_if:role,petugas',
        ]);

        $shift = $request->shift;

        if ($request->role === 'petugas') {
            if (!$shift) {
                return back()->withErrors(['shift' => 'Shift wajib untuk petugas']);
            }

            $status = $this->checkShift($shift);
        } else {
            $shift = null;
            $status = 'aktif';
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'shift' => $shift,
            'status' => $status,
        ]);

        return redirect()->route('user');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'required',
            'shift' => 'required_if:role,petugas',
            'status' => 'required_if:role,admin,owner'
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;

        if ($request->role == 'petugas') {
            $user->shift = $request->shift;
            $user->status = $this->checkShift($request->shift);
        } else {
            $user->shift = null;
            $user->status = 'aktif';
        }

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $this->logAktivitas('Edit user: ' . $user->name);

        return redirect()->route('user');
    }

    // =========================
    // SHIFT SYSTEM
    // =========================

    public function updateStatusShiftPetugas()
    {
        $jam = now()->format('H:i');

        $petugas = User::where('role', 'petugas')->get();

        foreach ($petugas as $user) {

            if ($user->shift == 'pagi') {
                $user->status = ($jam >= '06:00' && $jam < '14:00')
                    ? 'aktif' : 'nonaktif';
            }

            if ($user->shift == 'siang') {
                $user->status = ($jam >= '14:00' && $jam < '22:00')
                    ? 'aktif' : 'nonaktif';
            }

            if ($user->shift == 'malam') {
                $user->status = ($jam >= '22:00' || $jam < '06:00')
                    ? 'aktif' : 'nonaktif';
            }

            $user->save();
        }
    }

    public function checkShift($shift)
    {
        $jam = now()->format('H:i');

        if ($shift == 'pagi') {
            return ($jam >= '06:00' && $jam < '14:00') ? 'aktif' : 'nonaktif';
        }

        if ($shift == 'siang') {
            return ($jam >= '14:00' && $jam < '22:00') ? 'aktif' : 'nonaktif';
        }

        if ($shift == 'malam') {
            return ($jam >= '22:00' || $jam < '06:00') ? 'aktif' : 'nonaktif';
        }

        return 'nonaktif';
    }

    // =========================
    // LOG AKTIVITAS
    // =========================

    public function logAktivitas($text)
    {
        DB::table('t_log_aktivitas')->insert([
            'id_user' => auth()->id() ?? null,
            'aktivitas' => $text,
            'waktu_aktivitas' => now()
        ]);
    }
}