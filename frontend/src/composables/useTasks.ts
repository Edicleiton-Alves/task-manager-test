import { computed, ref, watch } from 'vue'
import { api } from '@/api/http'

export type Task = {
    id: number
    project_id: number
    title: string
    description: string | null
    status: 'todo' | 'in_progress' | 'done'
    priority: 'low' | 'medium' | 'high'
    due_date: string | null
    is_overdue: boolean
}

type CursorPaginated<T> = {
    data: T[]
    next_cursor?: string | null
    prev_cursor?: string | null
    links?: unknown
    meta?: unknown
}

function buildQuery(params: Record<string, string | undefined>) {
    const qs = new URLSearchParams()
    Object.entries(params).forEach(([k, v]) => {
        if (v) qs.set(k, v)
    })
    const s = qs.toString()
    return s ? `?${s}` : ''
}

export function useTasks(projectId: number) {
    const tasks = ref<Task[]>([])
    const loading = ref(false)
    const error = ref<string | null>(null)
    const success = ref<string | null>(null)

    const status = ref<string>('')     
    const priority = ref<string>('')  
    const overdue = ref(false)

    const debouncedStatus = ref(status.value)
    const debouncedPriority = ref(priority.value)
    const debouncedOverdue = ref(overdue.value)

    let debounceTimer: number | undefined

    watch([status, priority, overdue], () => {
        window.clearTimeout(debounceTimer)
        debounceTimer = window.setTimeout(() => {
            debouncedStatus.value = status.value
            debouncedPriority.value = priority.value
            debouncedOverdue.value = overdue.value
        }, 350)
    })

    const queryString = computed(() =>
        buildQuery({
            status: debouncedStatus.value || undefined,
            priority: debouncedPriority.value || undefined,
            overdue: debouncedOverdue.value ? '1' : undefined,
        }),
    )

    async function fetchTasks() {
        loading.value = true
        error.value = null
        success.value = null

        try {
            const res = await api<CursorPaginated<Task>>(`/projects/${projectId}/tasks${queryString.value}`)
            tasks.value = res.data
            success.value = 'Tarefas carregadas'
        } catch (e: any) {
            error.value = e?.message ?? 'Erro ao carregar tarefas'
        } finally {
            loading.value = false
        }
    }

    watch(queryString, () => {
        fetchTasks()
    })

    async function createTask(payload: Omit<Task, 'id' | 'project_id' | 'is_overdue'>) {
        loading.value = true
        error.value = null
        success.value = null

        try {
            const res = await api<{ data: Task }>(`/projects/${projectId}/tasks`, {
                method: 'POST',
                body: payload,
            })
            tasks.value = [res.data, ...tasks.value]
            success.value = 'Tarefa criada'
            return res.data
        } catch (e: any) {
            error.value = e?.message ?? 'Erro ao criar tarefa'
            throw e
        } finally {
            loading.value = false
        }
    }

    async function updateTaskOptimistic(
        taskId: number,
        patch: Partial<Pick<Task, 'status' | 'priority'>>
    ) {
        error.value = null
        success.value = null

        const idx = tasks.value.findIndex((t) => t.id === taskId)
        if (idx === -1) return

        const current = tasks.value[idx]
        if (!current) return

        const before: Task = { ...current }

        tasks.value[idx] = { ...current, ...patch }

        try {
            const res = await api<{ data: Task }>(`/tasks/${taskId}`, {
                method: 'PATCH',
                body: patch,
            })

            tasks.value[idx] = res.data
            success.value = 'Tarefa atualizada'
        } catch (e: any) {
            tasks.value[idx] = before
            error.value = e?.message ?? 'Erro ao atualizar tarefa'
            throw e
        }
    }

    async function deleteTask(taskId: number) {
        error.value = null
        success.value = null

        const before: Task[] = [...tasks.value]

        tasks.value = tasks.value.filter((t) => t.id !== taskId)

        try {
            await api<void>(`/tasks/${taskId}`, { method: 'DELETE' })
            success.value = 'Tarefa removida'
        } catch (e: any) {
            tasks.value = before
            error.value = e?.message ?? 'Erro ao remover tarefa'
            throw e
        }
    }

    return {
        tasks,
        loading,
        error,
        success,
        status,
        priority,
        overdue,
        fetchTasks,
        createTask,
        updateTaskOptimistic,
        deleteTask,
    }
}