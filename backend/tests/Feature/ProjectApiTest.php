<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_projects_creates_a_project_and_returns_resource(): void
    {
        $payload = [
            'name' => 'Meu Projeto',
            'description' => 'Descrição do projeto',
            'status' => 'active',
        ];

        $res = $this->postJson('/api/projects', $payload);

        $res->assertCreated();
        $res->assertJsonStructure([
            'data' => ['id', 'name', 'description', 'status', 'tasks_count'],
        ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Meu Projeto',
            'description' => 'Descrição do projeto',
            'status' => 'active',
        ]);
    }

    public function test_get_projects_returns_list_with_tasks_count(): void
    {
        $p1 = Project::factory()->create(['status' => 'active']);
        $p2 = Project::factory()->create(['status' => 'archived']);

        Task::factory()->for($p1)->count(3)->create();
        Task::factory()->for($p2)->count(1)->create();

        $res = $this->getJson('/api/projects');

        $res->assertOk();
        $res->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'description', 'status', 'tasks_count'],
            ],
        ]);

        // Como o endpoint ordena por id desc, não confiamos na posição: buscamos pelo id.
        $items = collect($res->json('data'));
        $item1 = $items->firstWhere('id', $p1->id);
        $item2 = $items->firstWhere('id', $p2->id);

        $this->assertNotNull($item1);
        $this->assertNotNull($item2);
        $this->assertSame(3, $item1['tasks_count']);
        $this->assertSame(1, $item2['tasks_count']);
    }
}