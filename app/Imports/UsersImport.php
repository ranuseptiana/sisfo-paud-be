<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

class UsersImport implements ToModel, WithHeadingRow
{
    /**
     * Mengimpor data ke model User
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Pastikan password di-hash sebelum disimpan
        return new User([
            'name' => $row['name'],
            'username' => $row['username'],
            'username_verified_at' => $row['username_verified_at'],
            'password' => Hash::make($row['password']),
            'remember_token' => $row['username_verified_at'],
            'created_at' => $row['created_at'],
            'updated_at' => $row['updated_at'],
            'user_type' => $row['user_type'],
            'id_reference' => $row['id_reference'] ?? null,
        ]);
    }
}
