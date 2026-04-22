@extends('admin.layouts.app')
@section('content')
<h2 class="mb-4">Pengguna</h2>
<table class="table table-bordered table-hover">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Peran</th>
            <th>Tanggal Dibuat</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->is_admin ? 'Admin' : 'Pengguna' }}</td>
            <td>{{ $user->created_at->format('d/m/Y') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endsection
