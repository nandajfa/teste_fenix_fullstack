<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import PaginationBar from '@/components/PaginationBar.vue'
import { examService } from '@/services/examService'
import type { ApiError } from '@/services/api'
import type { Exam } from '@/types/api'

const route = useRoute()

const exams = ref<Exam[]>([])
const page = ref(1)
const lastPage = ref(1)
const loading = ref(true)
const error = ref<string | null>(null)
const success = ref<string | null>(route.query.saved ? 'Prova salva.' : null)

async function load(target = 1) {
  loading.value = true
  error.value = null
  try {
    const result = await examService.list(target)
    exams.value = result.data
    page.value = result.meta.current_page
    lastPage.value = result.meta.last_page
  } catch (e) {
    error.value = (e as ApiError).message
  } finally {
    loading.value = false
  }
}

async function remove(exam: Exam) {
  const warning = exam.attempts_count
    ? `"${exam.title}" já foi respondida por ${exam.attempts_count} aluno(s). Ela sai da lista, mas os resultados continuam no histórico. Excluir?`
    : `Excluir "${exam.title}"?`

  if (!confirm(warning)) return

  try {
    await examService.remove(exam.id)
    success.value = 'Prova excluída.'
    // Se era o último item da página, volta uma página
    await load(exams.value.length === 1 && page.value > 1 ? page.value - 1 : page.value)
  } catch (e) {
    error.value = (e as ApiError).message
  }
}

onMounted(() => load())
</script>

<template>
  <div class="page-header">
    <div>
      <h1>Provas</h1>
      <p class="muted">Crie, edite e exclua as provas da turma.</p>
    </div>
    <RouterLink to="/professor/provas/nova" class="btn btn-primary">Nova prova</RouterLink>
  </div>

  <p v-if="success" class="alert alert-success">{{ success }}</p>
  <p v-if="error" class="alert alert-error">{{ error }}</p>

  <p v-if="loading" class="muted">Carregando provas...</p>

  <div v-else-if="exams.length === 0" class="card">
    <h2>Nenhuma prova cadastrada</h2>
    <p class="muted">Crie a primeira prova para os alunos responderem.</p>
    <RouterLink to="/professor/provas/nova" class="btn btn-primary">Criar prova</RouterLink>
  </div>

  <template v-else>
    <ul class="exam-list">
      <li v-for="exam in exams" :key="exam.id" class="exam-item">
        <div class="exam-item-body">
          <div class="exam-item-title">{{ exam.title }}</div>
          <div class="muted">
            {{ exam.questions_count }} questões · {{ exam.attempts_count }} tentativas
          </div>
        </div>
        <div class="actions">
          <RouterLink :to="`/professor/provas/${exam.id}/editar`" class="btn">Editar</RouterLink>
          <button type="button" class="btn btn-danger" @click="remove(exam)">Excluir</button>
        </div>
      </li>
    </ul>

    <PaginationBar :page="page" :last-page="lastPage" @change="load" />
  </template>
</template>
