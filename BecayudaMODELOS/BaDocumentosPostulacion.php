<?php

namespace App\Http\Controllers\Api\Becayuda;

use App\Http\Controllers\Controller;
use App\Models\Becayuda\BaDocumentosPostulacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Filesystem\FilesystemAdapter;

class BaDocumentosPostulacionController extends Controller
{
    /**
     * Tipamos el disk para que Intelephense reconozca url(), download(), etc.
     */
    private function publicDisk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('public');
        return $disk;
    }

    /**
     * Estados permitidos por tu CHECK en Postgres
     */
    private function estadosPermitidos(): array
    {
        return ['pendiente', 'validado', 'observado', 'corregido'];
    }

    public function index(Request $request): JsonResponse
    {
        $q = BaDocumentosPostulacion::query()->orderBy('id', 'desc');

        if ($request->filled('id_postulacion')) {
            $q->where('id_postulacion', (int) $request->id_postulacion);
        }

        $disk = $this->publicDisk();

        $docs = $q->get()->map(function (BaDocumentosPostulacion $doc) use ($disk) {
            $url = $doc->ruta_archivo ? $disk->url($doc->ruta_archivo) : null;

            return [
                'id' => $doc->id,
                'id_postulacion' => $doc->id_postulacion,
                'nombre_documento' => $doc->nombre_documento,
                'ruta_archivo' => $doc->ruta_archivo,
                'url_publica' => $url,
                'estado_revision' => $doc->estado_revision,
                'observacion' => $doc->observacion,
                'id_user_revisor' => $doc->id_user_revisor,
                'created_at' => $doc->created_at,
                'updated_at' => $doc->updated_at,
            ];
        });

        return response()->json(['message' => 'OK', 'data' => $docs], 200);
    }

    public function show(int $id): JsonResponse
    {
        $doc = BaDocumentosPostulacion::find($id);
        if (!$doc) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        $disk = $this->publicDisk();
        $url = $doc->ruta_archivo ? $disk->url($doc->ruta_archivo) : null;

        return response()->json([
            'message' => 'OK',
            'data' => $doc,
            'url_publica' => $url,
        ], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $nombre = $request->input('nombre_documento', $request->input('nombre_docum'));
        $request->merge(['nombre_documento' => $nombre]);

        $request->validate([
            'id_postulacion' => ['required', 'integer'],
            'nombre_documento' => ['required', 'string'],
            'archivo' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $safeName = Str::slug($request->nombre_documento);
        $filename = $safeName . '_' . time() . '.pdf';

        $folder = "becayuda/postulacion_{$request->id_postulacion}";
        $path = $request->file('archivo')->storeAs($folder, $filename, 'public');

        $doc = BaDocumentosPostulacion::create([
            'id_postulacion' => (int) $request->id_postulacion,
            'nombre_documento' => $request->nombre_documento,
            'ruta_archivo' => $path,
            'estado_revision' => 'pendiente',
            'observacion' => null,
            'id_user_revisor' => null,
        ]);

        $disk = $this->publicDisk();

        return response()->json([
            'message' => 'Creado',
            'data' => $doc,
            'url_publica' => $doc->ruta_archivo ? $disk->url($doc->ruta_archivo) : null,
        ], 201);
    }

    // Route-model-binding: tu ruta debe ser {documento}
    public function update(Request $request, BaDocumentosPostulacion $documento): JsonResponse
    {
        $request->validate([
            'nombre_documento' => ['sometimes', 'string'],
            // IMPORTANTÍSIMO: que coincida con el CHECK de Postgres
            'estado_revision' => ['sometimes', Rule::in($this->estadosPermitidos())],
            // exists permite null (has/filled a veces lo ignoran)
            'observacion' => ['sometimes', 'nullable', 'string'],
            'id_user_revisor' => ['sometimes', 'nullable', 'integer'],
            'archivo' => ['sometimes', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        if ($request->filled('nombre_documento')) {
            $documento->nombre_documento = $request->input('nombre_documento');
        }

        if ($request->filled('estado_revision')) {
            $documento->estado_revision = $request->input('estado_revision');
        }

        if ($request->exists('observacion')) {
            $documento->observacion = $request->input('observacion'); // puede ser null
        }

        if ($request->exists('id_user_revisor')) {
            $documento->id_user_revisor = $request->input('id_user_revisor'); // puede ser null
        }

        // Reemplazo de archivo
        if ($request->hasFile('archivo')) {
            $disk = $this->publicDisk();

            if ($documento->ruta_archivo) {
                $disk->delete($documento->ruta_archivo);
            }

            $base = Str::slug($documento->nombre_documento ?: 'documento');
            $filename = $base . '_' . time() . '.pdf';
            $folder = "becayuda/postulacion_{$documento->id_postulacion}";
            $path = $request->file('archivo')->storeAs($folder, $filename, 'public');

            $documento->ruta_archivo = $path;
        }

        $documento->save();

        // Devolvemos lo que realmente quedó en BD
        $documento = $documento->fresh();

        $disk = $this->publicDisk();

        return response()->json([
            'message' => 'Actualizado',
            'data' => $documento,
            'url_publica' => $documento->ruta_archivo ? $disk->url($documento->ruta_archivo) : null,
        ], 200);
    }

    public function destroy(int $id): JsonResponse
    {
        $doc = BaDocumentosPostulacion::find($id);
        if (!$doc) {
            return response()->json(['message' => 'No encontrado'], 404);
        }

        $disk = $this->publicDisk();

        if ($doc->ruta_archivo) {
            $disk->delete($doc->ruta_archivo);
        }

        $doc->delete();

        return response()->json(['message' => 'Eliminado'], 200);
    }

    public function download(int $id)
    {
        $doc = BaDocumentosPostulacion::find($id);
        if (!$doc || !$doc->ruta_archivo) {
            return response()->json(['message' => 'Archivo no encontrado'], 404);
        }

        $disk = $this->publicDisk();

        if (!$disk->exists($doc->ruta_archivo)) {
            return response()->json(['message' => 'El archivo no existe en storage'], 404);
        }

        $nombre = Str::slug($doc->nombre_documento ?: 'documento') . '.pdf';
        return $disk->download($doc->ruta_archivo, $nombre);
    }
}
