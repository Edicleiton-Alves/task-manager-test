import { createRouter, createWebHistory } from 'vue-router'
import ProjectsListView from '../views/ProjectsListView.vue'
import ProjectDetailView from '../views/ProjectDetailView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    { path: '/', redirect: '/projects' },
    { path: '/projects', name: 'projects', component: ProjectsListView },
    { path: '/projects/:id', name: 'project-detail', component: ProjectDetailView, props: true },
  ],
})

export default router