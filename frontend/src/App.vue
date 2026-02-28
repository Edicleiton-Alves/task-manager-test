<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
</script>

<template>
  <div class="min-h-screen bg-zinc-950 text-zinc-100">
    <header class="sticky top-0 z-10 border-b border-zinc-800 bg-zinc-950/80 backdrop-blur">
      <div class="mx-auto max-w-5xl px-6 py-4 flex items-center justify-between">
        <RouterLink to="/projects" class="flex items-center gap-2">
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/15 text-emerald-400 font-bold">
            TM
          </span>
          <span class="font-semibold tracking-tight">Task Manager</span>
        </RouterLink>

        <nav class="text-sm">
          <RouterLink
            to="/projects"
            class="rounded-md px-3 py-2 hover:bg-zinc-900"
            active-class="bg-zinc-900 text-emerald-300"
          >
            Projetos
          </RouterLink>
        </nav>
      </div>
    </header>

    <main class="mx-auto max-w-5xl px-6 py-8">
      <RouterView />
    </main>
  </div>
</template>