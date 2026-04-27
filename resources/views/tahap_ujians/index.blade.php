@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="page-title mb-1">Tahap Ujian</h3>
        <div class="text-muted">Kelola master tahap ujian seperti Tes Kesehatan, Psikotes, Wawancara, dan lainnya.</div>
    </div>

    <a href="{{ route('tahap-ujians.create') }}" class="btn btn-primary">
        Tambah Tahap
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card page-card">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-striped table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th width="70">No</th>
                    <th>Nama Tahap</th>
                    <th>Status</th>
                    <th width="180">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tahapUjians as $tahap)
                    <tr>
                        <td>{{ $loop->iteration + ($tahapUjians->currentPage() - 1) * $tahapUjians->perPage() }}</td>
                        <td>{{ $tahap->nama }}</td>
                        <td>
                            @if($tahap->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-secondary">Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('tahap-ujians.edit', $tahap->id) }}" class="btn btn-warning btn-sm">
                                Edit
                            </a>

                            <form action="{{ route('tahap-ujians.destroy', $tahap->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Yakin hapus tahap ujian ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">
                                    Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">Belum ada tahap ujian.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-3 d-flex justify-content-center">
            {{ $tahapUjians->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection