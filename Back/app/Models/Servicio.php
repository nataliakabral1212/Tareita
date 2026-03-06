<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class Servicio
{
    public static function all(): array
    {
        return DB::select(
            "SELECT s.*, c.nombre AS categoria_nombre
             FROM servicios s
             INNER JOIN categorias c ON c.id = s.categoria_id
             ORDER BY s.id DESC"
        );
    }

    public static function find(int $id): ?object
    {
        $result = DB::select(
            "SELECT s.*, c.nombre AS categoria_nombre
             FROM servicios s
             INNER JOIN categorias c ON c.id = s.categoria_id
             WHERE s.id = ? LIMIT 1",
            [$id]
        );
        return $result[0] ?? null;
    }

    public static function create(array $data): int
    {
        DB::insert(
            "INSERT INTO servicios
                (nombre, categoria_id, duracion, precio, descripcion, estado, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())",
            [
                $data['nombre'],
                $data['categoriaId'],
                $data['duracion']    ?? 30,
                $data['precio']      ?? 0,
                $data['descripcion'] ?? null,
                $data['estado']      ?? 'activo',
            ]
        );

        return (int) DB::getPdo()->lastInsertId();
    }
}
