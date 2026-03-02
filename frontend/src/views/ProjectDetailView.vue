<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { RouterLink } from 'vue-router'
import { api } from '@/api/http'

const props = defineProps<{ id: string }>()

type Project = {
  id: number
  name: string
  description: string | null
  status: 'active' | 'archived'
  tasks_count: number
}

type Task = {
  id: number
  title: string
  description: string | null
  status: 'todo' | 'in_progress' | 'done'
  priority: 'low' | 'medium' | 'high'
  due_date: string | null
  is_overdue?: boolean
}

type ListResponse<T> =
  | { data: T[]; links?: unknown; meta?: unknown } // Laravel resource collection (paginated or not)
  | T[] // plain array

const error = ref<string | null>(null)
const success = ref<string | null>(null)

// --------------------
// Project
// --------------------
const project = ref<Project | null>(null)
const projectLoading = ref(false)

async function fetchProject() {
  projectLoading.value = true
  error.value = null

  try {
    const res = await api<ListResponse<Project>>('/api/projects')
    const list = Array.isArray(res) ? res : res.data
    const found = list.find((p) => String(p.id) === String(props.id))
    if (!found) throw new Error('Projeto não encontrado.')
    project.value = found
  } catch (e: any) {
    project.value = null
    error.value = e?.message ?? 'Erro ao carregar projeto.'
  } finally {
    projectLoading.value = false
  }
}

const statusPillClass = computed(() => {
  const st = project.value?.status
  if (st === 'active') return 'border-emerald-500/30 text-emerald-200 bg-emerald-500/10'
  return 'border-zinc-700 text-zinc-300 bg-zinc-800/40'
})

// --------------------
// Tasks list + filters (NO pagination)
// --------------------
const tasks = ref<Task[]>([])
const tasksLoading = ref(false)

const filterStatus = ref<'' | Task['status']>('')
const filterPriority = ref<'' | Task['priority']>('')
const filterOverdue = ref(false)

function buildQuery() {
  const params = new URLSearchParams()
  if (filterStatus.value) params.set('status', filterStatus.value)
  if (filterPriority.value) params.set('priority', filterPriority.value)
  if (filterOverdue.value) params.set('overdue', '1')
  return params.toString()
}

async function fetchTasks() {
  tasksLoading.value = true
  error.value = null

  try {
    const qs = buildQuery()
    const endpoint = `/api/projects/${encodeURIComponent(props.id)}/tasks${qs ? `?${qs}` : ''}`

    const res = await api<ListResponse<Task>>(endpoint)
    tasks.value = Array.isArray(res) ? res : res.data
  } catch (e: any) {
    tasks.value = []
    error.value = e?.message ?? 'Erro ao carregar tarefas.'
  } finally {
    tasksLoading.value = false
  }
}

// debounce filtros
let debounceTimer: number | undefined
watch([filterStatus, filterPriority, filterOverdue], () => {
  window.clearTimeout(debounceTimer)
  debounceTimer = window.setTimeout(() => fetchTasks(), 300)
})

// --------------------
// Modal: create/edit task
// --------------------
const isTaskModalOpen = ref(false)
const modalMode = ref<'create' | 'edit'>('create')
const editingTaskId = ref<number | null>(null)

const modalTitle = ref('')
const modalDescription = ref('')
const modalStatus = ref<Task['status']>('todo')
const modalPriority = ref<Task['priority']>('medium')
const modalDueDate = ref<string>('') // YYYY-MM-DD

const modalLoading = ref(false)
const modalError = ref<string | null>(null)

const canSubmitModal = computed(() => modalTitle.value.trim().length > 0)

function openCreateTaskModal() {
  modalMode.value = 'create'
  editingTaskId.value = null
  modalTitle.value = ''
  modalDescription.value = ''
  modalStatus.value = 'todo'
  modalPriority.value = 'medium'
  modalDueDate.value = ''
  modalError.value = null
  isTaskModalOpen.value = true
}

function openEditTaskModal(t: Task) {
  modalMode.value = 'edit'
  editingTaskId.value = t.id
  modalTitle.value = t.title
  modalDescription.value = t.description ?? ''
  modalStatus.value = t.status
  modalPriority.value = t.priority
  modalDueDate.value = t.due_date ?? ''
  modalError.value = null
  isTaskModalOpen.value = true
}

function closeTaskModal() {
  isTaskModalOpen.value = false
}

// ESC para fechar
function onKeydown(e: KeyboardEvent) {
  if (e.key === 'Escape' && isTaskModalOpen.value) closeTaskModal()
}

async function submitTaskModal() {
  if (!canSubmitModal.value) return

  modalLoading.value = true
  modalError.value = null
  error.value = null
  success.value = null

  try {
    const payload = {
      title: modalTitle.value.trim(),
      description: modalDescription.value.trim() || null,
      status: modalStatus.value,
      priority: modalPriority.value,
      due_date: modalDueDate.value || null,
    }

    if (modalMode.value === 'create') {
      const res = await api<{ data?: Task }>(`/api/projects/${props.id}/tasks`, {
        method: 'POST',
        body: payload,
      })

      const created: Task | undefined = (res as any)?.data
      if (created) {
        tasks.value = [created, ...tasks.value]
      } else {
        await fetchTasks()
      }

      if (project.value) project.value.tasks_count = (project.value.tasks_count ?? 0) + 1
      success.value = 'Tarefa criada com sucesso!'
      closeTaskModal()
      return
    }

    // edit
    if (!editingTaskId.value) throw new Error('Tarefa inválida para edição.')
    const taskId = editingTaskId.value

    const res = await api<{ data?: Task }>(`/api/tasks/${taskId}`, {
      method: 'PATCH',
      body: payload,
    })

    const updated: Task | undefined = (res as any)?.data
    if (updated) {
      tasks.value = tasks.value.map((t) => (t.id === taskId ? updated : t))
    } else {
      tasks.value = tasks.value.map((t) =>
        t.id === taskId ? { ...t, ...payload, due_date: payload.due_date } : t,
      )
    }

    success.value = 'Tarefa atualizada com sucesso!'
    closeTaskModal()
  } catch (e: any) {
    modalError.value = e?.message ?? 'Não foi possível salvar a tarefa.'
  } finally {
    modalLoading.value = false
  }
}

// --------------------
// Actions: delete only
// --------------------
async function deleteTask(taskId: number) {
  const snapshot = tasks.value.slice()
  tasks.value = tasks.value.filter((x) => x.id !== taskId)
  error.value = null
  success.value = null

  try {
    await api(`/api/tasks/${taskId}`, { method: 'DELETE' })
    if (project.value) project.value.tasks_count = Math.max(0, (project.value.tasks_count ?? 0) - 1)
    success.value = 'Tarefa removida.'
  } catch {
    tasks.value = snapshot
    error.value = 'Falha ao remover tarefa.'
  }
}

function taskStatusPillClass(st: Task['status']) {
  if (st === 'done') return 'border-emerald-500/30 text-emerald-200 bg-emerald-500/10'
  return 'border-zinc-700 text-zinc-300 bg-zinc-800/40'
}

function priorityPillClass(p: Task['priority']) {
  if (p === 'high') return 'border-emerald-500/30 text-emerald-200 bg-emerald-500/10'
  if (p === 'medium') return 'border-zinc-700 text-zinc-300 bg-zinc-800/40'
  return 'border-zinc-800 text-zinc-400 bg-zinc-900/30'
}

// --------------------
onMounted(() => {
  window.addEventListener('keydown', onKeydown)
  fetchProject()
  fetchTasks()
})

watch(
  () => props.id,
  () => {
    closeTaskModal()
    fetchProject()
    fetchTasks()
  },
)
</script>

<template>
  <div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col gap-2">
      <div class="flex items-start justify-between gap-4">
        <div class="min-w-0">
          <h1 class="text-3xl font-semibold tracking-tight">
            <span v-if="project">Projeto: {{ project.name }}</span>
            <span v-else>Projeto</span>
          </h1>
          <p v-if="project?.description" class="text-sm text-zinc-400">
            {{ project.description }}
          </p>
          <p v-else class="text-sm text-zinc-500">Sem descrição</p>
        </div>

        <div class="flex items-end gap-3">
          <span
            v-if="project"
            class="rounded-full px-2 py-0.5 text-xs border"
            :class="statusPillClass"
          >
            {{ project.status }}
          </span>

          <div v-if="project" class="text-right">
            <div class="text-xs text-zinc-500">tasks</div>
            <div class="text-lg font-semibold text-zinc-200">
              {{ project.tasks_count ?? '-' }}
            </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Alerts -->
    <div v-if="error" class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
      {{ error }}
    </div>
    <div v-else-if="success" class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
      {{ success }}
    </div>

    <!-- Skeleton project -->
    <div v-if="projectLoading" class="grid gap-3 sm:grid-cols-2">
      <div v-for="i in 2" :key="i" class="h-28 rounded-xl border border-zinc-800 bg-zinc-900/20 animate-pulse" />
    </div>

    <!-- Tasks list -->
    <section class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Tarefas</h2>

        <div class="flex items-center gap-3">
          <span class="text-sm text-zinc-400">{{ tasks.length }} tarefa(s)</span>

          <button
            type="button"
            class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-emerald-400 disabled:opacity-50"
            @click="openCreateTaskModal"
            :disabled="tasksLoading"
          >
            + Nova tarefa
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="grid gap-3 sm:grid-cols-3">
        <label class="grid gap-1 text-sm">
          <span class="text-zinc-300">Status</span>
          <select
            v-model="filterStatus"
            class="rounded-lg border border-zinc-800 bg-zinc-950/40 px-3 py-2 outline-none focus:border-emerald-500/60"
          >
            <option value="">todos</option>
            <option value="todo">todo</option>
            <option value="in_progress">in_progress</option>
            <option value="done">done</option>
          </select>
        </label>

        <label class="grid gap-1 text-sm">
          <span class="text-zinc-300">Prioridade</span>
          <select
            v-model="filterPriority"
            class="rounded-lg border border-zinc-800 bg-zinc-950/40 px-3 py-2 outline-none focus:border-emerald-500/60"
          >
            <option value="">todas</option>
            <option value="low">low</option>
            <option value="medium">medium</option>
            <option value="high">high</option>
          </select>
        </label>

        <label class="flex items-center gap-2 rounded-lg border border-zinc-800 bg-zinc-950/40 px-3 py-2 text-sm">
          <input type="checkbox" v-model="filterOverdue" />
          <span class="text-zinc-300">Apenas overdue</span>
        </label>
      </div>

      <!-- Loading skeleton -->
      <div v-if="tasksLoading" class="grid gap-3 sm:grid-cols-2">
        <div v-for="i in 4" :key="i" class="h-28 rounded-xl border border-zinc-800 bg-zinc-900/20 animate-pulse" />
      </div>

      <div v-else-if="tasks.length === 0" class="rounded-xl border border-zinc-800 bg-zinc-900/20 p-6 text-sm text-zinc-300">
        Nenhuma tarefa encontrada. Clique em “Nova tarefa” para criar.
      </div>

      <div v-else class="grid gap-3 sm:grid-cols-2">
        <div
          v-for="t in tasks"
          :key="t.id"
          class="group rounded-xl border border-zinc-800 bg-zinc-900/20 p-5 hover:border-emerald-500/40 hover:bg-zinc-900/30 transition"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="flex items-center gap-2 flex-wrap">
                <h3 class="truncate font-semibold text-zinc-100 group-hover:text-emerald-200">
                  {{ t.title }}
                </h3>

                <span class="rounded-full px-2 py-0.5 text-xs border" :class="taskStatusPillClass(t.status)">
                  {{ t.status }}
                </span>

                <span class="rounded-full px-2 py-0.5 text-xs border" :class="priorityPillClass(t.priority)">
                  {{ t.priority }}
                </span>

                <span
                  v-if="t.is_overdue"
                  class="rounded-full px-2 py-0.5 text-xs border border-red-500/30 text-red-200 bg-red-500/10"
                >
                  overdue
                </span>
              </div>

              <p v-if="t.description" class="mt-1 line-clamp-2 text-sm text-zinc-400">
                {{ t.description }}
              </p>
              <p v-else class="mt-1 text-sm text-zinc-500">Sem descrição</p>

              <div v-if="t.due_date" class="mt-3 text-xs text-zinc-500">
                Due: {{ t.due_date }}
              </div>
            </div>

            <!-- ONLY Edit + Remove -->
            <div class="flex flex-col items-end gap-2">
              <div class="flex gap-2 flex-wrap justify-end">
                <button
                  type="button"
                  class="rounded-lg border border-zinc-800 px-3 py-2 text-xs hover:bg-zinc-900"
                  @click="openEditTaskModal(t)"
                >
                  Editar
                </button>

                <button
                  type="button"
                  class="rounded-lg border border-zinc-800 px-3 py-2 text-xs hover:bg-zinc-900"
                  @click="deleteTask(t.id)"
                >
                  Remover
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- MODAL -->
    <div
      v-if="isTaskModalOpen"
      class="fixed inset-0 z-50 flex items-center justify-center"
      aria-modal="true"
      role="dialog"
      @click.self="closeTaskModal"
    >
      <div class="absolute inset-0 bg-black/60" />

      <div class="relative w-full max-w-lg rounded-xl border border-zinc-800 bg-zinc-950 p-5 shadow-xl">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h3 class="text-lg font-semibold">
              {{ modalMode === 'create' ? 'Nova tarefa' : 'Editar tarefa' }}
            </h3>
            <p class="text-sm text-zinc-400">
              {{ modalMode === 'create' ? 'Preencha e salve para adicionar à lista.' : 'Atualize os dados e salve.' }}
            </p>
          </div>

          <button
            type="button"
            class="rounded-lg border border-zinc-800 px-3 py-2 text-xs hover:bg-zinc-900"
            @click="closeTaskModal"
          >
            Fechar (Esc)
          </button>
        </div>

        <div v-if="modalError" class="mt-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
          {{ modalError }}
        </div>

        <div class="mt-4 grid gap-3 sm:grid-cols-2">
          <label class="grid gap-1 text-sm sm:col-span-2">
            <span class="text-zinc-300">Título *</span>
            <input
              v-model="modalTitle"
              type="text"
              class="rounded-lg border border-zinc-800 bg-zinc-950/40 px-3 py-2 outline-none focus:border-emerald-500/60"
              placeholder="Ex: Ajustar TaskCard"
            />
          </label>

          <label class="grid gap-1 text-sm">
            <span class="text-zinc-300">Status</span>
            <select
              v-model="modalStatus"
              class="rounded-lg border border-zinc-800 bg-zinc-950/40 px-3 py-2 outline-none focus:border-emerald-500/60"
            >
              <option value="todo">todo</option>
              <option value="in_progress">in_progress</option>
              <option value="done">done</option>
            </select>
          </label>

          <label class="grid gap-1 text-sm">
            <span class="text-zinc-300">Prioridade</span>
            <select
              v-model="modalPriority"
              class="rounded-lg border border-zinc-800 bg-zinc-950/40 px-3 py-2 outline-none focus:border-emerald-500/60"
            >
              <option value="low">low</option>
              <option value="medium">medium</option>
              <option value="high">high</option>
            </select>
          </label>

          <label class="grid gap-1 text-sm">
            <span class="text-zinc-300">Due date</span>
            <input
              v-model="modalDueDate"
              type="date"
              class="rounded-lg border border-zinc-800 bg-zinc-950/40 px-3 py-2 outline-none focus:border-emerald-500/60"
            />
          </label>

          <label class="grid gap-1 text-sm sm:col-span-2">
            <span class="text-zinc-300">Descrição</span>
            <textarea
              v-model="modalDescription"
              rows="3"
              class="rounded-lg border border-zinc-800 bg-zinc-950/40 px-3 py-2 outline-none focus:border-emerald-500/60"
              placeholder="Opcional"
            />
          </label>

          <div class="sm:col-span-2 flex items-center justify-end gap-3">
            <button
              type="button"
              class="rounded-lg border border-zinc-800 px-4 py-2 text-sm hover:bg-zinc-900"
              @click="closeTaskModal"
              :disabled="modalLoading"
            >
              Cancelar
            </button>

            <button
              type="button"
              class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-emerald-400 disabled:opacity-50"
              @click="submitTaskModal"
              :disabled="modalLoading || !canSubmitModal"
            >
              {{ modalLoading ? 'Salvando...' : (modalMode === 'create' ? 'Criar' : 'Salvar') }}
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>