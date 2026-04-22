@extends('admin.layouts.app')
@section('content')
<h2 class="mb-4">Lapangan</h2>
<a href="{{ route('admin.courts.create') }}" class="btn btn-primary mb-3">+ Tambah Lapangan</a>
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>Gambar</th>
            <th>Nama</th>
            <th>Harga/Jam</th>
            <th>Status</th>
            <th>Dibuat</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($courts as $court)
        <tr>
            <td>@if($court->image_path)<img src="{{ asset('storage/'.$court->image_path) }}" width="80">@endif</td>
            <td>{{ $court->name }}</td>
            <td>Rp {{ number_format($court->price_per_hour,0,',','.') }}/jam</td>
            <td>
                <form method="POST" action="{{ route('admin.courts.change-status', $court) }}">
                    @csrf
                    <button class="btn btn-sm {{ $court->status=='active' ? 'btn-success' : 'btn-warning' }}">{{ ucfirst($court->status) }}</button>
                </form>
            </td>
            <td>{{ $court->created_at->format('d/m/Y') }}</td>
            <td>
                <a href="{{ route('admin.courts.edit', $court) }}" class="btn btn-sm btn-info">Edit</a>
                <form action="{{ route('admin.courts.destroy', $court) }}" method="POST" style="display:inline-block">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus lapangan ini?')">Hapus</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
