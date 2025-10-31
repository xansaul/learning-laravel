<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Http\Resources\TaskResource;

class TaskController extends Controller
{

    public function __construct()
    {

        $this->authorizeResource(Task::class, 'task', [
            'except' => ['index', 'store'],
        ]);
    }

    public function index(Project $project)
    {

        $this->authorize('view', $project);


        return response()->json($project->tasks);
    }


    public function store(Request $request, Project $project)
    {
        // Esta autorización ya usa el $project inyectado, ¡lo cual es correcto!
        $this->authorize('update', $project);

        $data_validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'string|nullable',
            'status' => 'sometimes|in:pending,in_progress,completed',
        ]);

        $task =  $request->user()->tasks()->create([
            'title' => $data_validated['title'],
            'description' => $validated['description'] ?? null,
            'project_id' => $project->id,
        ]);
        return response()->json($task, 201);
    }


    public function show(Task $task)
    {

        return response()->json($task);
    }

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


    public function destroy(Task $task)
    {

        $task->delete();

        return response()->json(null, 204);
    }
}
