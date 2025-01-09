@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Daftar Kelas</h1>

    <a href="{{ route('kelas.create') }}" class="btn btn-primary mb-3">Tambah Kelas</a>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama Kelas</th>
                <th>Admin</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($kelas as $k)
                <tr>
                    <td>{{ $k->id }}</td>
                    <td>{{ $k->nama_kelas }}</td>
                    <td>{{ $k->admin->email_admin }}</td> <!-- Relasi ke Admin -->
                    <td>
                        <form action="{{ route('kelas.destroy', $k->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
