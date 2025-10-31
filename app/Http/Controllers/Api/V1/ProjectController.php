<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA; // <-- Importante

/**
 * @OA\Tag(
 * name="Projects",
 * description="Endpoints para gestionar los proyectos de un usuario"
 * )
 *
 * @OA\Schema(
 * schema="Project",
 * title="Project",
 * description="Modelo de Proyecto",
 * @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 * @OA\Property(property="name", type="string", example="Mi Primer Proyecto"),
 * @OA\Property(property="description", type="string", nullable=true, example="Descripción de mi proyecto."),
 * @OA\Property(property="user_id", type="integer", readOnly="true", example="1"),
 * @OA\Property(property="created_at", type="string", format="date-time", readOnly="true"),
 * @OA\Property(property="updated_at", type="string", format="date-time", readOnly="true")
 * )
 */
class ProjectController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Project::class, 'project');
    }

    /**
     * @OA\Get(
     * path="/api/projects",
     * tags={"Projects"},
     * summary="Listar proyectos del usuario",
     * description="Obtiene todos los proyectos asociados al usuario autenticado.",
     * security={{"bearerAuth": {}}},
     * @OA\Response(
     * response=200,
     * description="Lista de proyectos",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/Project")
     * )
     * ),
     * @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function index(Request $request)
    {
        return $request->user()->projects;
    }

    /**
     * @OA\Post(
     * path="/api/projects",
     * tags={"Projects"},
     * summary="Crear un nuevo proyecto",
     * description="Crea un nuevo proyecto y lo asocia al usuario autenticado.",
     * security={{"bearerAuth": {}}},
     * @OA\RequestBody(
     * required=true,
     * description="Datos del nuevo proyecto",
     * @OA\JsonContent(
     * required={"name"},
     * @OA\Property(property="name", type="string", maxLength=255, example="Nuevo Proyecto"),
     * @OA\Property(property="description", type="string", nullable=true, example="Descripción opcional.")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Proyecto creado exitosamente",
     * @OA\JsonContent(ref="#/components/schemas/Project")
     * ),
     * @OA\Response(response=422, description="Datos de validación inválidos"),
     * @OA\Response(response=401, description="No autenticado")
     * )
     */
    public function store(Request $request)
    {
        $data_validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'string|nullable',
        ]);

        $product = $request->user()->projects()->create($data_validated);

        $product->refresh();

        return response()->json($product->toArray(), 201);
    }

    /**
     * @OA\Get(
     * path="/api/projects/{project}",
     * tags={"Projects"},
     * summary="Obtener un proyecto específico",
     * description="Muestra los detalles de un proyecto específico, si el usuario tiene permiso.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="project",
     * in="path",
     * required=true,
     * description="ID del proyecto",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Detalles del proyecto",
     * @OA\JsonContent(ref="#/components/schemas/Project")
     * ),
     * @OA\Response(response=401, description="No autenticado"),
     * @OA\Response(response=403, description="No autorizado"),
     * @OA\Response(response=404, description="Proyecto no encontrado")
     * )
     */
    public function show(Request $request, Project $project)
    {
        return $project;
    }

    /**
     * @OA\Put(
     * path="/api/projects/{project}",
     * tags={"Projects"},
     * summary="Actualizar un proyecto",
     * description="Actualiza los datos de un proyecto específico.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="project",
     * in="path",
     * required=true,
     * description="ID del proyecto",
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Datos a actualizar",
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", maxLength=255, example="Proyecto Actualizado"),
     * @OA\Property(property="description", type="string", nullable=true, example="Nueva descripción.")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Proyecto actualizado",
     * @OA\JsonContent(ref="#/components/schemas/Project")
     * ),
     * @OA\Response(response=401, description="No autenticado"),
     * @OA\Response(response=403, description="No autorizado"),
     * @OA\Response(response=404, description="Proyecto no encontrado"),
     * @OA\Response(response=422, description="Datos de validación inválidos")
     * )
     */
    public function update(Request $request, Project $project)
    {
        // Nota: Tu validación de 'description' era 'numeric', lo cual parecía un error
        // dado que en store() era 'string'. La he documentado como 'string'.
        // Ajusta la anotación @OA\Property si 'numeric' era intencional.
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|nullable', // Cambié esto para que coincida con store()
        ]);

        $project->update($request->all());

        return response()->json($project, 200);
    }

    /**
     * @OA\Delete(
     * path="/api/projects/{project}",
     * tags={"Projects"},
     * summary="Eliminar un proyecto",
     * description="Elimina un proyecto específico.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="project",
     * in="path",
     * required=true,
     * description="ID del proyecto",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=204,
     * description="Proyecto eliminado (Sin contenido)"
     * ),
     * @OA\Response(response=401, description="No autenticado"),
     * @OA\Response(response=403, description="No autorizado"),
     * @OA\Response(response=404, description="Proyecto no encontrado")
     * )
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json(null, 204);
    }
}
