<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { studentService } from '@/services/studentService'
import type { ApiError } from '@/services/api'
import type { Attempt, StudentExamSummary } from '@/types/api'

const props = defineProps<{ studentId: string }>()
const studentId = Number(props.studentId)

const exams = ref<StudentExamSummary[]>([])
const history = ref<Attempt[]>([])
const loading = ref(true)
const error = ref<string | null>(null)

const pending = computed(() => exams.value.filter((exam) => exam.attempt === null))

const formatDate = (iso: string) => new Date(iso).toLocaleDateString('pt-BR')

onMounted(async () => {
  try {
    // As duas requisições em paralelo
    ;[exams.value, history.value] = await Promise.all([
      studentService.exams(studentId),
      studentService.attempts(studentId),
    ])
  } catch (e) {
    error.value = (e as ApiError).message
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <h1>Minhas provas</h1>

  <p v-if="loading" class="muted">Carregando provas...</p>
  <p v-else-if="error" class="alert alert-error">{{ error }}</p>

  <template v-else>
    <section class="section">
      <h2>Para fazer</h2>
      <p v-if="pending.length === 0" class="muted">Você não tem provas pendentes.</p>
      <ul v-else class="exam-list">
        <li v-for="exam in pending" :key="exam.id" class="exam-item">
          <div class="exam-item-body">
            <div class="exam-item-title">{{ exam.title }}</div>
            <div class="muted">{{ exam.questions_count }} questões</div>
          </div>
          <RouterLink :to="`/aluno/${studentId}/provas/${exam.id}`" class="btn btn-primary">Fazer prova</RouterLink>
        </li>
      </ul>
    </section>

    <section class="section">
      <h2>Realizadas</h2>
      <p v-if="history.length === 0" class="muted">Você ainda não fez nenhuma prova.</p>
      <ul v-else class="exam-list">
        <li v-for="attempt in history" :key="attempt.id" class="exam-item">
          <div class="exam-item-body">
            <div class="exam-item-title">
              {{ attempt.exam?.title }}
              <span v-if="attempt.exam?.is_deleted" class="tag">removida</span>
            </div>
            <div class="muted">
              Enviada em {{ formatDate(attempt.submitted_at) }} · {{ attempt.correct_count }} de
              {{ attempt.total_questions }} acertos
            </div>
          </div>
          <div class="score">{{ attempt.percentage }}%</div>
          <RouterLink :to="`/aluno/${studentId}/resultados/${attempt.id}`" class="btn">Ver resultado</RouterLink>
        </li>
      </ul>
    </section>
  </template>
</template>
