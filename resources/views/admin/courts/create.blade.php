@extends('admin.layouts.app')
@section('content')
<h2 class="mb-4">Buat Lapangan</h2>
<form action="{{ route('admin.courts.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="mb-3">
        <label class="form-label">Nama Lapangan</label>
        <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Harga Per Jam (IDR)</label>
        <input type="number" name="price_per_hour" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Gambar Lapangan</label>
        <input type="file" name="image_path" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
            <option value="active">Aktif</option>
            <option value="maintenance">Perawatan</option>
        </select>
    </div>
    <button class="btn btn-success">Simpan Lapangan</button>
    <a href="{{ route('admin.courts.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection
