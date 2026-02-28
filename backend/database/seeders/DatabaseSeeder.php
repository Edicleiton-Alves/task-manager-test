<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Task;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Project::factory(10)
            ->create()
            ->each(function ($project) {
                Task::factory(rand(10, 30))
                    ->create([
                        'project_id' => $project->id,
                    ]);
            });
    }
}