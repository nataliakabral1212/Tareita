<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    public function index(): JsonResponse
    {
        $servicios = Servicio::all();
        return response()->json($servicios, 200);
    }

    public function show(int $id): JsonResponse
    {
        $servicio = Servicio::find($id);

        if (!$servicio) {
            return response()->json(['message' => 'Servicio no encontrado'], 404);
        }

        return response()->json($servicio, 200);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nombre'      => 'required|string|max:150',
            'categoriaId' => 'required|integer',
            'duracion'    => 'nullable|integer|min:1',
            'precio'      => 'nullable|numeric|min:0',
            'descripcion' => 'nullable|string',
            'estado'      => 'nullable|in:activo,inactivo',
        ]);

        $categoria = Categoria::find((int) $request->categoriaId);
        if (!$categoria) {
            return response()->json([
                'message' => 'La categoría seleccionada no existe.',
            ], 422);
        }

        $id       = Servicio::create($request->only(
            'nombre', 'categoriaId', 'duracion', 'precio', 'descripcion', 'estado'
        ));
        $servicio = Servicio::find($id);

        return response()->json([
            'message' => 'Servicio creado exitosamente',
            'data'    => $servicio,
        ], 201);
    }
}
