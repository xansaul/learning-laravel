<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;


class ProjectController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Project::class, 'project');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return $request->user()->projects;
    }

    /**
     * Store a newly created resource in storage.
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
     * Display the specified resource.
     */
    public function show(Request $request, Project $project)
    {
        return $project;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|numeric|min:0',
        ]);

        $project->update($request->all());

        return response()->json($project, 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $project->delete();

        return response()->json(null, 204);
    }
}
