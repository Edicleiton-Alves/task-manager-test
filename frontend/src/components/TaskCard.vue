<script setup lang="ts">
import type { Task } from '@/composables/useTasks'

const props = defineProps<{
  task: Task
  onChangeStatus?: (taskId: number, status: Task['status']) => void
  onChangePriority?: (taskId: number, priority: Task['priority']) => void
  onDelete?: (taskId: number) => void
}>()

function handleStatus(e: Event) {
  const value = (e.target as HTMLSelectElement).value as Task['status']
  props.onChangeStatus?.(props.task.id, value)
}

function handlePriority(e: Event) {
  const value = (e.target as HTMLSelectElement).value as Task['priority']
  props.onChangePriority?.(props.task.id, value)
}
</script>

<template>
  <div class="rounded border p-4 space-y-2">
    <div class="flex items-start justify-between gap-4">
      <div>
        <h3 class="font-semibold">
          {{ task.title }}
          <span v-if="task.is_overdue" class="ml-2 text-sm text-red-600">Atrasada</span>
        </h3>
        <p v-if="task.description" class="text-sm opacity-80">{{ task.description }}</p>
        <p class="text-xs opacity-70" v-if="task.due_date">Vencimento: {{ task.due_date }}</p>
      </div>

      <button
        class="text-sm underline"
        type="button"
        @click="onDelete?.(task.id)"
      >
        Remover
      </button>
    </div>

    <div class="flex gap-3 items-center">
      <label class="text-sm">
        Status:
        <select class="border rounded px-2 py-1" :value="task.status" @change="handleStatus">
          <option value="todo">todo</option>
          <option value="in_progress">in_progress</option>
          <option value="done">done</option>
        </select>
      </label>

      <label class="text-sm">
        Prioridade:
        <select class="border rounded px-2 py-1" :value="task.priority" @change="handlePriority">
          <option value="low">low</option>
          <option value="medium">medium</option>
          <option value="high">high</option>
        </select>
      </label>
    </div>
  </div>
</template>