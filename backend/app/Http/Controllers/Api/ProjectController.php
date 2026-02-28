<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectController extends Controller
{
    /**
     * GET /api/projects
     */
    public function index(): AnonymousResourceCollection
    {
        $projects = Project::query()
            ->withCount('tasks')
            ->orderByDesc('id')
            ->paginate(10);

        return ProjectResource::collection($projects);
    }

    /**
     * POST /api/projects
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        $data = $request->validated();

        $project = Project::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);

        return (new ProjectResource($project->loadCount('tasks')))
            ->response()
            ->setStatusCode(201);
    }
}
