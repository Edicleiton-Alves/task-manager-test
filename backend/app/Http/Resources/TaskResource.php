<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $dueDate = $this->due_date; // cast pra date ajuda (Model casts)

        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'priority' => $this->priority,
            'due_date' => $dueDate?->format('Y-m-d'),

            // campo calculado útil pro front
            'is_overdue' => $dueDate
                ? $dueDate->isPast() && $this->status !== 'done'
                : false,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
