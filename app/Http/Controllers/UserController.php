<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
 HEAD


    public function index()
    {
        // 🔥 update status petugas setiap load halaman (simple real-time tanpa scheduler)
        $this->updateStatusShiftPetugas();

        $admin = User::where('role', 'admin')->get();
        $petugas = User::where('role', 'petugas')->get();
        $owner = User::where('role', 'owner')->get();

        return view('User.user', compact('admin', 'petugas', 'owner'));
    }

    public function create()
    {
        $user = null;
        return view('User.create', compact('user'));
    }



    public function index()
    {
        $users = User::latest()->get();
        return view('User.user', compact('users'));
    }


    public function create()
    {
        return view('User.create', ['user' => null]);
    }


 f474ab34b311fe87a9b8fd39b467fa9d9b20fc34
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('User.create', compact('user'));
    }

HEAD
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


    // ===============================
    // SIMPAN USER (TANPA STATUS)
    // ===============================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4',
            'role' => 'required'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        $this->logAktivitas("Tambah user: {$user->name}");

        return redirect()->route('user')->with('success', 'User berhasil ditambahkan');
    }

    // ===============================
    // UPDATE USER (TANPA STATUS)
    // ===============================
 f474ab34b311fe87a9b8fd39b467fa9d9b20fc34
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required',
 HEAD
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

            // jangan manual, nanti di-handle system shift
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
    // 🔥 SHIFT SYSTEM CORE
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

            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required'
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role
        ];

        // 🔥 FIX PASSWORD
        if (!empty($request->password)) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        $this->logAktivitas("Edit user: {$user->name}");

        return redirect()->route('user')->with('success', 'User berhasil diupdate');
    }

    // ===============================
    // DELETE USER
    // ===============================
    public function delete($id)
    {
        $user = User::find($id);

        if (!$user) {
            return redirect()->route('user')->with('error', 'User tidak ditemukan');
        }

        $nama = $user->name;
        $user->delete();

        $this->logAktivitas("Hapus user: {$nama}");

        return redirect()->route('user')->with('success', 'User berhasil dihapus');
    }

    // ===============================
    // LOG AKTIVITAS
    // ===============================
    private function logAktivitas($text)
    {
        DB::table('t_log_aktivitas')->insert([
            'id_user' => auth()->id(),
 f474ab34b311fe87a9b8fd39b467fa9d9b20fc34
            'aktivitas' => $text,
            'waktu_aktivitas' => now()
        ]);
    }
}
