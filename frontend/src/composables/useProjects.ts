import { ref } from 'vue'
import { api } from '@/api/http'

export type Project = {
  id: number
  name: string
  description: string | null
  status: 'active' | 'archived'
  tasks_count?: number
}

type Paginated<T> = {
  data: T[]
  links?: unknown
  meta?: unknown
}

export function useProjects() {
  const projects = ref<Project[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const success = ref<string | null>(null)

  async function fetchProjects() {
    loading.value = true
    error.value = null
    success.value = null

    try {
      const res = await api<Paginated<Project>>('/api/projects')
      projects.value = res.data
      success.value = 'Projetos carregados'
    } catch (e: any) {
      error.value = e?.message ?? 'Erro ao carregar projetos'
    } finally {
      loading.value = false
    }
  }

  async function createProject(payload: Pick<Project, 'name' | 'description' | 'status'>) {
    loading.value = true
    error.value = null
    success.value = null

    try {
      const res = await api<{ data: Project }>('/api/projects', { method: 'POST', body: payload })
      projects.value = [res.data, ...projects.value]
      success.value = 'Projeto criado'
      return res.data
    } catch (e: any) {
      error.value = e?.message ?? 'Erro ao criar projeto'
      throw e
    } finally {
      loading.value = false
    }
  }

  return { projects, loading, error, success, fetchProjects, createProject }
}