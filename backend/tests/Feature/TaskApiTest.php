<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_project_tasks_creates_task_for_project(): void
    {
        $project = Project::factory()->create();

        $payload = [
            'title' => 'Minha task',
            'description' => 'Detalhes',
            'status' => 'todo',
            'priority' => 'high',
            'due_date' => now()->addDays(3)->toDateString(),
        ];

        $res = $this->postJson("/api/projects/{$project->id}/tasks", $payload);

        $res->assertCreated();
        $res->assertJsonStructure([
            'data' => ['id', 'title', 'description', 'status', 'priority', 'due_date', 'is_overdue'],
        ]);

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title' => 'Minha task',
            'status' => 'todo',
            'priority' => 'high',
        ]);
    }

    public function test_patch_task_updates_fields(): void
    {
        $project = Project::factory()->create();
        $task = Task::factory()->for($project)->create([
            'status' => 'todo',
            'priority' => 'low',
        ]);

        $payload = [
            'status' => 'done',
            'priority' => 'high',
        ];

        $res = $this->patchJson("/api/tasks/{$task->id}", $payload);

        $res->assertOk();
        $res->assertJsonPath('data.id', $task->id);
        $res->assertJsonPath('data.status', 'done');
        $res->assertJsonPath('data.priority', 'high');

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'done',
            'priority' => 'high',
        ]);
    }

    public function test_delete_task_soft_deletes_task(): void
    {
        $project = Project::factory()->create();
        $task = Task::factory()->for($project)->create();

        $res = $this->deleteJson("/api/tasks/{$task->id}");

        $res->assertNoContent();
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}