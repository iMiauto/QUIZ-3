<?php

namespace App\Http\Controllers;

use App\Models\Curso;
use Illuminate\Http\Request;

class CursoController extends Controller
{
    public function store(Request $request)
    {
        $datos = $request->validate([
            'sigla' => 'required|string|max:10|unique:cursos,sigla',
            'nombreCurso' => 'required|string|max:100',
            'creditos' => 'required|integer|min:1|max:255',
        ]);

        $curso = Curso::create($datos);

        return response()->json($curso, 201);
    }
}
