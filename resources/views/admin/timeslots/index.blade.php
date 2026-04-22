@extends('admin.layouts.app')
@section('content')
<h2 class="mb-4">Kelola Slot Waktu</h2>
<a href="{{ route('admin.timeslots.create') }}" class="btn btn-primary mb-3">+ Tambah Slot Waktu</a>
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>Slot Waktu</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach($timeSlots as $slot)
        <tr>
            <td>{{ $slot->display_text }}</td>
            <td>
                <span class="badge {{ $slot->status=='active' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($slot->status) }}</span>
            </td>
            <td>
                <a href="{{ route('admin.timeslots.edit', $slot) }}" class="btn btn-sm btn-info">Edit</a>
                <form action="{{ route('admin.timeslots.destroy', $slot) }}" method="POST" style="display:inline-block">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus slot waktu ini?')">Hapus</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
