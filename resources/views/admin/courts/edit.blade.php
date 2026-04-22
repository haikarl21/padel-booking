@extends('admin.layouts.app')
@section('content')
<h2 class="mb-4">Edit Lapangan</h2>
<form action="{{ route('admin.courts.update', $court) }}" method="POST" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="mb-3">
        <label class="form-label">Nama Lapangan</label>
        <input type="text" name="name" class="form-control" value="{{ $court->name }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Harga Per Jam (IDR)</label>
        <input type="number" name="price_per_hour" class="form-control" value="{{ $court->price_per_hour }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Gambar Saat Ini</label><br>
        @if($court->image_path)
            <img src="{{ asset('storage/'.$court->image_path) }}" width="120">
        @endif
    </div>
    <div class="mb-3">
        <label class="form-label">Ganti Gambar</label>
        <input type="file" name="image_path" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
            <option value="active" @if($court->status=='active') selected @endif>Aktif</option>
            <option value="maintenance" @if($court->status=='maintenance') selected @endif>Perawatan</option>
        </select>
    </div>
    <button class="btn btn-warning">Perbarui Lapangan</button>
    <a href="{{ route('admin.courts.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection
