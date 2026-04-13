@extends('layouts.app')
<<<<<<< HEAD
@section('content')

<div class="card p-4">

    <h4>
        {{ $user ? 'Edit User' : 'Tambah User' }}
    </h4>

    <form method="POST"
        action="{{ $user ? route('user.update',$user->id) : route('user.store') }}">

        @csrf

        @if($user)
        @method('PUT')
        @endif

        <!-- NAMA -->
        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="name" class="form-control"
                value="{{ old('name', $user->name ?? '') }}">
        </div>

        <!-- EMAIL -->
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control"
                value="{{ old('email', $user->email ?? '') }}">
        </div>

        <!-- PASSWORD -->
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control">
            <small>Kosongkan jika tidak ingin mengganti password</small>
        </div>

        <!-- ROLE -->
        <div class="mb-3">
            <label>Role</label>

            <select name="role" class="form-control" id="roleSelect">
                <option value="admin" {{ ($user->role ?? '')=='admin'?'selected':'' }}>Admin</option>
                <option value="petugas" {{ ($user->role ?? '')=='petugas'?'selected':'' }}>Petugas</option>
                <option value="owner" {{ ($user->role ?? '')=='owner'?'selected':'' }}>Owner</option>
            </select>
        </div>

        <!-- SHIFT (WAJIB ADA name="shift") -->
        <div class="mb-3" id="shiftBox">
            <label>Shift</label>

            <select name="shift" id="shiftSelect" class="form-control">
                <option value="">-- Pilih Shift --</option>

                <option value="pagi" {{ ($user->shift ?? '')=='pagi'?'selected':'' }}>Pagi</option>
                <option value="siang" {{ ($user->shift ?? '')=='siang'?'selected':'' }}>Siang</option>
                <option value="malam" {{ ($user->shift ?? '')=='malam'?'selected':'' }}>Malam</option>
            </select>
        </div>

        <!-- STATUS -->
        <div class="mb-3">
            <label>Status</label>

            <select name="status" class="form-control" id="statusSelect">
                <option value="aktif" {{ ($user->status ?? '')=='aktif'?'selected':'' }}>Aktif</option>
                <option value="nonaktif" {{ ($user->status ?? '')=='nonaktif'?'selected':'' }}>Nonaktif</option>
            </select>
        </div>

        <button class="btn btn-primary">
            {{ $user ? 'Update' : 'Simpan' }}
        </button>

    </form>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const roleSelect = document.getElementById("roleSelect");
        const shiftSelect = document.getElementById("shiftSelect");
        const shiftBox = document.getElementById("shiftBox");
        const statusSelect = document.getElementById("statusSelect");

        function toggleShift() {
            let role = roleSelect.value;

            if (role === "petugas") {
                shiftBox.style.display = "block";
                shiftSelect.disabled = false;
            } else {
                shiftBox.style.display = "none";
                shiftSelect.disabled = true;
                shiftSelect.value = "";
            }
        }

        function updateStatus() {
            let role = roleSelect.value;

            if (role !== "petugas") return;

            let shift = shiftSelect.value;
            let hour = new Date().getHours();

            let status = "nonaktif";

            if (shift === "pagi") {
                status = (hour >= 6 && hour < 14) ? "aktif" : "nonaktif";
            } else if (shift === "siang") {
                status = (hour >= 14 && hour < 22) ? "aktif" : "nonaktif";
            } else if (shift === "malam") {
                status = (hour >= 22 || hour < 6) ? "aktif" : "nonaktif";
            }

            statusSelect.value = status;
        }

        roleSelect.addEventListener("change", toggleShift);
        shiftSelect.addEventListener("change", updateStatus);

        toggleShift();
    });
</script>
=======
@section('title', $user ? 'Edit User' : 'Tambah User')

@section('content')

<!-- PAGE TITLE -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">
                {{ $user ? 'Edit User' : 'Tambah User' }}
            </h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item">User</li>
                    <li class="breadcrumb-item active">
                        {{ $user ? 'Edit' : 'Tambah' }}
                    </li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- FORM -->
<div class="row">
    <div class="col-lg-6">

        <div class="card">
            <div class="card-body">

                <h4 class="card-title mb-4">
                    {{ $user ? 'Edit Data User' : 'Tambah Data User' }}
                </h4>

                <form method="POST"
                    action="{{ $user ? route('user.update',$user->id) : route('user.store') }}">
                    @csrf

                    <!-- NAMA -->
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name', $user->name ?? '') }}"
                            required>

                        @error('name')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- EMAIL -->
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email"
                            name="email"
                            class="form-control"
                            value="{{ old('email', $user->email ?? '') }}"
                            required>

                        @error('email')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- PASSWORD -->
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password"
                            name="password"
                            class="form-control">

                        <small class="text-muted">
                            Kosongkan jika tidak ingin mengganti password
                        </small>

                        @error('password')
                        <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <!-- ROLE -->
                    <div class="mb-3">
                        <label class="form-label">Role</label>

                        <select name="role" class="form-select" required>
                            <option value="admin" {{ old('role',$user->role ?? '')=='admin'?'selected':'' }}>
                                Admin
                            </option>

                            <option value="petugas" {{ old('role',$user->role ?? '')=='petugas'?'selected':'' }}>
                                Petugas
                            </option>

                            <option value="owner" {{ old('role',$user->role ?? '')=='owner'?'selected':'' }}>
                                Owner
                            </option>
                        </select>
                    </div>

                    <!-- BUTTON -->
                    <div class="d-flex justify-content-between">

                        <button class="btn btn-primary">
                            {{ $user ? 'Update' : 'Simpan' }}
                        </button>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>
>>>>>>> f474ab34b311fe87a9b8fd39b467fa9d9b20fc34

@endsection