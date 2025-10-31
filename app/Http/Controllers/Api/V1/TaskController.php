<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 * name="Tasks",
 * description="Endpoints para gestionar tareas dentro de un proyecto"
 * )
 *
 * @OA\Schema(
 * schema="Task",
 * title="Task",
 * description="Modelo de Tarea",
 * @OA\Property(property="id", type="integer", readOnly="true", example="1"),
 * @OA\Property(property="title", type="string", example="Hacer la Tarea 1"),
 * @OA\Property(property="description", type="string", nullable=true, example="Descripción de la tarea."),
 * @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed"}, example="pending"),
 * @OA\Property(property="project_id", type="integer", readOnly="true", example="1"),
 * @OA\Property(property="user_id", type="string", format="uuid", nullable=true, readOnly="true", description="ID del usuario creador o asignado"),
 * @OA\Property(property="created_at", type="string", format="date-time", readOnly="true"),
 * @OA\Property(property="updated_at", type="string", format="date-time", readOnly="true")
 * )
 */
class TaskController extends Controller
{

    public function __construct()
    {

        $this->authorizeResource(Task::class, 'task', [
            'except' => ['index', 'store'],
        ]);
    }

    /**
     * @OA\Get(
     * path="/api/projects/{project}/tasks",
     * tags={"Tasks"},
     * summary="Listar tareas de un proyecto",
     * description="Obtiene todas las tareas asociadas a un proyecto específico.",
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
     * description="Lista de tareas",
     * @OA\JsonContent(
     * type="array",
     * @OA\Items(ref="#/components/schemas/Task")
     * )
     * ),
     * @OA\Response(response=401, description="No autenticado"),
     * @OA\Response(response=403, description="No autorizado"),
     * @OA\Response(response=404, description="Proyecto no encontrado")
     * )
     */
    public function index(Project $project)
    {

        $this->authorize('view', $project);


        return response()->json($project->tasks);
    }

    /**
     * @OA\Post(
     * path="/api/projects/{project}/tasks",
     * tags={"Tasks"},
     * summary="Crear una nueva tarea en un proyecto",
     * description="Crea una nueva tarea y la asocia a un proyecto y al usuario autenticado.",
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
     * description="Datos de la nueva tarea",
     * @OA\JsonContent(
     * required={"title"},
     * @OA\Property(property="title", type="string", maxLength=255, example="Nueva Tarea"),
     * @OA\Property(property="description", type="string", nullable=true, example="Descripción opcional."),
     * @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed"}, example="pending", description="El estado inicial (opcional).")
     * )
     * ),
     * @OA\Response(
     * response=201,
     * description="Tarea creada exitosamente",
     * @OA\JsonContent(ref="#/components/schemas/Task")
     * ),
     * @OA\Response(response=401, description="No autenticado"),
     * @OA\Response(response=403, description="No autorizado"),
     * @OA\Response(response=404, description="Proyecto no encontrado"),
     * @OA\Response(response=422, description="Datos de validación inválidos")
     * )
     */
    public function store(Request $request, Project $project)
    {

        $this->authorize('update', $project);

        $data_validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'string|nullable',
            'status' => 'sometimes|in:pending,in_progress,completed',
        ]);

        $task =  $request->user()->tasks()->create([
            'title' => $data_validated['title'],
            'description' => $data_validated['description'] ?? null,
            'project_id' => $project->id,
        ]);
        return response()->json($task, 201);
    }

    /**
     * @OA\Get(
     * path="/api/tasks/{task}",
     * tags={"Tasks"},
     * summary="Obtener una tarea específica",
     * description="Muestra los detalles de una tarea específica.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="task",
     * in="path",
     * required=true,
     * description="ID de la tarea",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=200,
     * description="Detalles de la tarea",
     * @OA\JsonContent(ref="#/components/schemas/Task")
     * ),
     * @OA\Response(response=401, description="No autenticado"),
     * @OA\Response(response=403, description="No autorizado"),
     * @OA\Response(response=404, description="Tarea no encontrada")
     * )
     */
    public function show(Task $task)
    {

        return response()->json($task);
    }

    /**
     * @OA\Put(
     * path="/api/tasks/{task}",
     * tags={"Tasks"},
     * summary="Actualizar una tarea",
     * description="Actualiza los datos de una tarea específica.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="task",
     * in="path",
     * required=true,
     * description="ID de la tarea",
     * @OA\Schema(type="integer")
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="Datos a actualizar",
     * @OA\JsonContent(
     * @OA\Property(property="title", type="string", maxLength=255, example="Tarea Actualizada"),
     * @OA\Property(property="description", type="string", nullable=true, example="Nueva descripción."),
     * @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed"}, example="in_progress"),
     * @OA\Property(property="user_id", type="string", format="uuid", nullable=true, description="Re-asignar tarea a otro usuario (ID debe ser UUID)")
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Tarea actualizada",
     * @OA\JsonContent(ref="#/components/schemas/Task")
     * ),
     * @OA\Response(response=401, description="No autenticado"),
     * @OA\Response(response=403, description="No autorizado"),
     * @OA\Response(response=404, description="Tarea no encontrada"),
     * @OA\Response(response=422, description="Datos de validación inválidos")
     * )
     */
    public function update(Request $request, Task $task)
    {

        $data = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|nullable',
            'status' => 'sometimes|in:pending,in_progress,completed',
            'user_id' => 'sometimes|nullable|uuid|exists:users,id',
        ]);

        $task->update($data);

        return response()->json($task);
    }

    /**
     * @OA\Delete(
     * path="/api/tasks/{task}",
     * tags={"Tasks"},
     * summary="Eliminar una tarea",
     * description="Elimina una tarea específica.",
     * security={{"bearerAuth": {}}},
     * @OA\Parameter(
     * name="task",
     * in="path",
     * required=true,
     * description="ID de la tarea",
     * @OA\Schema(type="integer")
     * ),
     * @OA\Response(
     * response=204,
     * description="Tarea eliminada (Sin contenido)"
     * ),
     * @OA\Response(response=401, description="No autenticado"),
     * @OA\Response(response=403, description="No autorizado"),
     * @OA\Response(response=404, description="Tarea no encontrada")
     * )
     */
    public function destroy(Task $task)
    {

        $task->delete();

        return response()->json(null, 204);
    }
}
