import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  { path: '/', name: 'profile', component: () => import('@/views/ProfileSelectView.vue') },

  { path: '/aluno', name: 'student-select', component: () => import('@/views/StudentSelectView.vue') },
  { path: '/professor/provas', name: 'professor-exams', component: () => import('@/views/ProfessorExamsView.vue') },
  { path: '/professor/provas/nova', name: 'exam-create', component: () => import('@/views/ExamFormView.vue') },
  { path: '/professor/provas/:id/editar', name: 'exam-edit', component: () => import('@/views/ExamFormView.vue'), props: true },
  { path: '/professor/dashboard', name: 'dashboard', component: () => import('@/views/DashboardView.vue') },

  { path: '/aluno/:studentId/provas', name: 'student-exams', component: () => import('@/views/StudentExamsView.vue'), props: true },
  { path: '/aluno/:studentId/provas/:examId', name: 'take-exam', component: () => import('@/views/TakeExamView.vue'), props: true },
  { path: '/aluno/:studentId/resultados/:attemptId', name: 'attempt-result', component: () => import('@/views/AttemptResultView.vue'), props: true },

  { path: '/:pathMatch(.*)*', redirect: '/' },
]

export default createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})