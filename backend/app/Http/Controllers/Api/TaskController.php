<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    /**
     * GET /api/projects/{project}/tasks?status=todo&priority=high&overdue=1
     */
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        $query = $project->tasks()->newQuery();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($priority = $request->query('priority')) {
            $query->where('priority', $priority);
        }

        if ($request->boolean('overdue')) {
            $query->overdue();
        }

        $tasks = $query
            ->orderByDesc('id')
            ->cursorPaginate(10)
            ->withQueryString();

        return TaskResource::collection($tasks);
    }

    /**
     * POST /api/projects/{project}/tasks
     */
    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $data = $request->validated();

        $task = $project->tasks()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'] ?? 'todo',
            'priority' => $data['priority'] ?? 'medium',
            'due_date' => $data['due_date'] ?? null,
        ]);

        return (new TaskResource($task))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * PATCH /api/tasks/{task}
     */
    public function update(UpdateTaskRequest $request, Task $task): TaskResource
    {
        $task->update($request->validated());

        return new TaskResource($task->fresh());
    }

    /**
     * DELETE /api/tasks/{task}  (soft delete)
     */
    public function destroy(Task $task): JsonResponse
    {
        $task->delete();

        return response()->json(null, 204);
    }
}