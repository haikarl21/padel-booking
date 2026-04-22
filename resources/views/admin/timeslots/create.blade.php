@extends('admin.layouts.app')
@section('content')
<h2 class="mb-4">Buat Slot Waktu</h2>
<form action="{{ route('admin.timeslots.store') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label class="form-label">Waktu Mulai</label>
        <input type="time" name="start_time" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Waktu Akhir</label>
        <input type="time" name="end_time" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
            <option value="active">Aktif</option>
            <option value="inactive">Tidak Aktif</option>
        </select>
    </div>
    <button class="btn btn-success">Simpan Slot Waktu</button>
    <a href="{{ route('admin.timeslots.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection
