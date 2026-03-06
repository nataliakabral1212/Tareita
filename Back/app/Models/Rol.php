<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Rol
{
    public static function all(): array
    {
        return DB::select("SELECT * FROM roles ORDER BY id DESC");
    }

    public static function find(int $id): ?object
    {
        $result = DB::select("SELECT * FROM roles WHERE id = ? LIMIT 1", [$id]);
        return $result[0] ?? null;
    }

    public static function create(array $data): int
    {
        DB::insert(
            "INSERT INTO roles (nombre, descripcion, estado, created_at, updated_at)
             VALUES (?, ?, ?, NOW(), NOW())",
            [
                $data['nombre'],
                $data['descripcion'] ?? null,
                $data['estado']      ?? 'activo',
            ]
        );

        return (int) DB::getPdo()->lastInsertId();
    }
}