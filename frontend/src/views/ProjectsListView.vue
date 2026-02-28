<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { useProjects } from '@/composables/useProjects'

const { projects, loading, error, success, fetchProjects, createProject } = useProjects()

const name = ref('')
const description = ref('')
const status = ref<'active' | 'archived'>('active')

const canCreate = computed(() => name.value.trim().length > 0)

async function onCreate() {
  if (!canCreate.value) return

  await createProject({
    name: name.value.trim(),
    description: description.value.trim() || null,
    status: status.value,
  })

  name.value = ''
  description.value = ''
  status.value = 'active'
}

onMounted(fetchProjects)
</script>

<template>
  <div class="space-y-8">
    <div class="flex flex-col gap-2">
      <h1 class="text-3xl font-semibold tracking-tight">Projetos</h1>
      <p class="text-sm text-zinc-400">
        Crie e gerencie projetos e tarefas.
      </p>
    </div>

    <!-- Alerts -->
    <div v-if="error" class="rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-200">
      {{ error }}
    </div>
    <div v-else-if="success" class="rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
      {{ success }}
    </div>

    <!-- Create project -->
    <section class="rounded-xl border border-zinc-800 bg-zinc-900/30 p-5">
      <div class="flex items-center justify-between gap-4">
        <div>
          <h2 class="text-lg font-semibold">Novo projeto</h2>
          <p class="text-sm text-zinc-400">Preencha os dados e clique em criar.</p>
        </div>
      </div>

      <div class="mt-4 grid gap-3 sm:grid-cols-2">
        <label class="grid gap-1 text-sm">
          <span class="text-zinc-300">Nome *</span>
          <input
            v-model="name"
            type="text"
            class="rounded-lg border border-zinc-800 bg-zinc-950/40 px-3 py-2 outline-none focus:border-emerald-500/60"
            placeholder="Ex: App de tarefas"
          />
        </label>

        <label class="grid gap-1 text-sm">
          <span class="text-zinc-300">Status</span>
          <select
            v-model="status"
            class="rounded-lg border border-zinc-800 bg-zinc-950/40 px-3 py-2 outline-none focus:border-emerald-500/60"
          >
            <option value="active">active</option>
            <option value="archived">archived</option>
          </select>
        </label>

        <label class="grid gap-1 text-sm sm:col-span-2">
          <span class="text-zinc-300">Descrição</span>
          <textarea
            v-model="description"
            rows="3"
            class="rounded-lg border border-zinc-800 bg-zinc-950/40 px-3 py-2 outline-none focus:border-emerald-500/60"
            placeholder="Opcional"
          />
        </label>

        <div class="sm:col-span-2 flex items-center justify-end gap-3">
          <button
            type="button"
            class="rounded-lg border border-zinc-800 px-4 py-2 text-sm hover:bg-zinc-900"
            @click="fetchProjects"
            :disabled="loading"
          >
            Recarregar
          </button>

          <button
            type="button"
            class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-zinc-950 hover:bg-emerald-400 disabled:opacity-50"
            @click="onCreate"
            :disabled="loading || !canCreate"
          >
            {{ loading ? 'Processando...' : 'Criar projeto' }}
          </button>
        </div>
      </div>
    </section>

    <!-- Projects list -->
    <section class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-lg font-semibold">Lista</h2>
        <span class="text-sm text-zinc-400">{{ projects.length }} projeto(s)</span>
      </div>

      <div v-if="loading" class="grid gap-3 sm:grid-cols-2">
        <div v-for="i in 4" :key="i" class="h-28 rounded-xl border border-zinc-800 bg-zinc-900/20 animate-pulse" />
      </div>

      <div v-else-if="projects.length === 0" class="rounded-xl border border-zinc-800 bg-zinc-900/20 p-6 text-sm text-zinc-300">
        Nenhum projeto ainda. Crie o primeiro acima.
      </div>

      <div v-else class="grid gap-3 sm:grid-cols-2">
        <RouterLink
          v-for="p in projects"
          :key="p.id"
          :to="`/projects/${p.id}`"
          class="group rounded-xl border border-zinc-800 bg-zinc-900/20 p-5 hover:border-emerald-500/40 hover:bg-zinc-900/30 transition"
        >
          <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
              <div class="flex items-center gap-2">
                <h3 class="truncate font-semibold text-zinc-100 group-hover:text-emerald-200">
                  {{ p.name }}
                </h3>
                <span
                  class="rounded-full px-2 py-0.5 text-xs border"
                  :class="p.status === 'active'
                    ? 'border-emerald-500/30 text-emerald-200 bg-emerald-500/10'
                    : 'border-zinc-700 text-zinc-300 bg-zinc-800/40'"
                >
                  {{ p.status }}
                </span>
              </div>

              <p v-if="p.description" class="mt-1 line-clamp-2 text-sm text-zinc-400">
                {{ p.description }}
              </p>
              <p v-else class="mt-1 text-sm text-zinc-500">
                Sem descrição
              </p>
            </div>

            <div class="text-right">
              <div class="text-xs text-zinc-500">tasks</div>
              <div class="text-lg font-semibold text-zinc-200">
                {{ p.tasks_count ?? '-' }}
              </div>
            </div>
          </div>

          <div class="mt-4 text-sm text-emerald-300 opacity-0 group-hover:opacity-100 transition">
            Abrir →
          </div>
        </RouterLink>
      </div>
    </section>
  </div>
</template>