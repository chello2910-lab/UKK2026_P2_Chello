@extends('layouts.app')

@section('content')

<div class="container-fluid">

    <div class="row">

        <!-- FORM -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Transaksi Parkir Baru</h5>
                </div>

                <div class="card-body">

                    <form action="{{ route('Transaksi.masuk') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Kendaraan</label>
                            <select name="id_kendaraan" class="form-control" required>
                                <option value="">-- Pilih Kendaraan --</option>
                                @foreach($kendaraanTersedia as $k)
                                <option value="{{ $k->id }}">
                                    {{ $k->plat_kendaraan }} - {{ $k->jenis_kendaraan }}
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Area Parkir</label>
                            <select name="id_area" class="form-control" required>
                                <option value="">-- Pilih Area --</option>
                                @foreach($area as $a)
                                <option value="{{ $a->id }}"
                                    {{ ($terisiPerArea[$a->id] ?? 0) >= $a->kapasitas ? 'disabled' : '' }}>
                                    {{ $a->nama_area }}
                                    ({{ $terisiPerArea[$a->id] ?? 0 }}/{{ $a->kapasitas }})
                                </option>
                                @endforeach
                            </select>
                        </div>

                        <button class="btn btn-primary w-100">
                            Masuk
                        </button>

                    </form>

                </div>
            </div>
        </div>


        <!-- TABEL -->
        <div class="col-md-8">
            <div class="card">

                <div class="card-header">
                    <h5 class="mb-0">Data Transaksi</h5>
                </div>

                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle table-nowrap">

                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Plat</th>
                                    <th>Jenis</th>
                                    <th>Area</th>
                                    <th>Waktu</th>
                                    <th>Durasi</th>
                                    <th>Biaya</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach($transaksi as $i => $t)
                                <tr data-status="{{ $t->status }}">

                                    <td>{{ $i+1 }}</td>
                                    <td><strong>{{ $t->plat_kendaraan }}</strong></td>
                                    <td>{{ $t->jenis_kendaraan }}</td>
                                    <td>{{ $t->nama_area }}</td>

                                    <td data-waktu="{{ $t->waktu_masuk }}">
                                        {{ \Carbon\Carbon::parse($t->waktu_masuk)->format('d M Y H:i') }}
                                    </td>

                                    <td class="durasi" data-tarif="{{ $t->tarif_per_jam }}">
                                        @if($t->status == 'parkir')
                                        0 jam 0 menit
                                        @else
                                        {{ $t->durasi }}
                                        @endif
                                    </td>

                                    <td class="biaya">
                                        @if($t->status == 'parkir')
                                        Rp 0
                                        @else
                                        Rp {{ number_format($t->biaya_total,0,',','.') }}
                                        @endif
                                    </td>

                                    <td>
                                        @if($t->status == 'parkir')
                                        <span class="badge bg-warning">Parkir</span>
                                        @elseif($t->status == 'keluar')
                                        <span class="badge bg-info">Menunggu</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($t->status == 'parkir')
                                        <button class="btn btn-success btn-sm keluar-button"
                                            data-id="{{ $t->id }}">
                                            Keluar
                                        </button>

                                        @elseif($t->status == 'keluar')
                                        <button class="btn btn-warning btn-sm pilih-bayar-button"
                                            data-id="{{ $t->id }}"
                                            data-biaya="{{ $t->biaya_total }}">
                                            Bayar
                                        </button>
                                        @endif
                                    </td>

                                </tr>
                                @endforeach
                            </tbody>

                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>

</div>

<!-- MODAL -->

<div class="modal fade" id="modalBayar">
    <div class="modal-dialog">
        <div class="modal-content">

            ```
            <div class="modal-header">
                <h5>Pembayaran</h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <h5 id="totalBayar"></h5>

                <input type="number" id="uangBayar" class="form-control mb-2" placeholder="Uang bayar">
                <input type="text" id="kembalian" class="form-control mb-3" placeholder="Kembalian" readonly>

                <button class="btn btn-success w-100 mb-2" id="btnCash">Cash</button>
            </div>

        </div>
    </div>

</div>

<script>
    let selectedId = null;
    let selectedBiaya = 0;

    // DURASI REALTIME
    document.addEventListener('DOMContentLoaded', function() {

        function updateDurasi() {

            document.querySelectorAll('table tbody tr').forEach(row => {

                const status = row.dataset.status;
                if (status !== 'parkir') return;

                const tdDurasi = row.querySelector('.durasi');
                const tdBiaya = row.querySelector('.biaya');

                const tarif = parseInt(tdDurasi.dataset.tarif);
                const waktuMasuk = new Date(row.querySelector('[data-waktu]').dataset.waktu);

                const now = new Date();
                const diffMs = now - waktuMasuk;

                const totalMinutes = Math.floor(diffMs / 1000 / 60);
                const jam = Math.floor(totalMinutes / 60);
                const menit = totalMinutes % 60;

                tdDurasi.textContent = jam + ' jam ' + menit + ' menit';

                const tarifPerMenit = tarif / 60;
                const biaya = Math.floor(totalMinutes * tarifPerMenit);

                tdBiaya.textContent = 'Rp ' + biaya.toLocaleString('id-ID');
            });
        }

        updateDurasi();
        setInterval(updateDurasi, 60000);
    });

    // EVENT
    document.addEventListener('click', function(e) {

        // keluar
        if (e.target.classList.contains('keluar-button')) {
            const id = e.target.dataset.id;

            fetch(`/Transaksi/keluar/${id}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(() => location.reload());
        }

        // buka modal
        if (e.target.classList.contains('pilih-bayar-button')) {
            selectedId = e.target.dataset.id;
            selectedBiaya = parseInt(e.target.dataset.biaya);

            document.getElementById('totalBayar').innerText =
                'Total: Rp ' + selectedBiaya.toLocaleString('id-ID');

            new bootstrap.Modal(document.getElementById('modalBayar')).show();
        }
    });

    // kembalian
    document.getElementById('uangBayar').addEventListener('input', function() {
        const uang = parseInt(this.value) || 0;
        const kembali = uang - selectedBiaya;

        document.getElementById('kembalian').value =
            kembali >= 0 ? 'Rp ' + kembali.toLocaleString('id-ID') : 'Uang kurang';
    });

    // CASH
    document.getElementById('btnCash').onclick = function() {

        const uang = parseInt(document.getElementById('uangBayar').value);

        if (!uang || uang < selectedBiaya) {
            alert('Uang kurang!');
            return;
        }

        fetch(`/Transaksi/bayar-cash/${selectedId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    uang_dibayar: uang
                })
            })
            .then(res => res.json())
            .then(() => {
                window.open(`/struk/${selectedId}`, '_blank');
                location.reload();
            });
    };
</script>

@endsection