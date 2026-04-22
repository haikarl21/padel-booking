@extends('admin.layouts.app')
@section('content')
<h2 class="mb-4">Edit Slot Waktu</h2>
<form action="{{ route('admin.timeslots.update', $timeSlot) }}" method="POST">
    @csrf @method('PUT')
    <div class="mb-3">
        <label class="form-label">Waktu Mulai</label>
        <input type="time" name="start_time" class="form-control" value="{{ $timeSlot->start_time }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Waktu Akhir</label>
        <input type="time" name="end_time" class="form-control" value="{{ $timeSlot->end_time }}" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
            <option value="active" @if($timeSlot->status=='active') selected @endif>Aktif</option>
            <option value="inactive" @if($timeSlot->status=='inactive') selected @endif>Tidak Aktif</option>
        </select>
    </div>
    <button class="btn btn-warning">Perbarui Slot Waktu</button>
    <a href="{{ route('admin.timeslots.index') }}" class="btn btn-secondary">Batal</a>
</form>
@endsection
