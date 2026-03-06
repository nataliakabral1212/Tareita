<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Rol::all();
        return response()->json($roles, 200);
    }

    public function show(int $id): JsonResponse
    {
        $rol = Rol::find($id);

        if (!$rol) {
            return response()->json(['message' => 'Rol no encontrado'], 404);
        }

        return response()->json($rol, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre'      => 'required|string|max:100',
            'descripcion' => 'nullable|string',
            'estado'      => 'nullable|in:activo,inactivo',
        ]);

        $id  = Rol::create($request->only('nombre', 'descripcion', 'estado'));
        $rol = Rol::find($id);

        return response()->json([
            'message' => 'Rol creado exitosamente',
            'data'    => $rol,
        ], 201);
    }
}