<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { studentService } from '@/services/studentService'
import type { ApiError } from '@/services/api'
import type { Student } from '@/types/api'

const students = ref<Student[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

const initials = (name: string): string =>
  name
    .split(' ')
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join('')
    .toUpperCase()

onMounted(async () => {
  try {
    students.value = await studentService.list()
  } catch (e) {
    error.value = (e as ApiError).message
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <section>
    <RouterLink to="/" class="back-link">Voltar</RouterLink>
    <h1>Quem é você?</h1>
    <p class="lead">Escolha seu nome para ver suas provas.</p>

    <p v-if="loading" class="muted">Carregando alunos...</p>
    <p v-else-if="error" class="alert alert-error">{{ error }}</p>
    <p v-else-if="students.length === 0" class="muted">Nenhum aluno cadastrado ainda.</p>

    <ul v-else class="student-grid">
      <li v-for="student in students" :key="student.id">
        <RouterLink :to="`/aluno/${student.id}/provas`" class="student">
          <span class="avatar" aria-hidden="true">{{ initials(student.name) }}</span>
          {{ student.name }}
        </RouterLink>
      </li>
    </ul>
  </section>
</template>