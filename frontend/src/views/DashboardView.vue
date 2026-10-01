<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import PaginationBar from '@/components/PaginationBar.vue'
import { dashboardService } from '@/services/dashboardService'
import type { ApiError } from '@/services/api'
import type { DashboardSummary, RankingEntry } from '@/types/api'

const summary = ref<DashboardSummary | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

const ranking = ref<RankingEntry[]>([])
const page = ref(1)
const lastPage = ref(1)
const examFilter = ref<number | null>(null)
const rankingLoading = ref(false)
const rankingError = ref<string | null>(null)

// 87.5 → "87,5%" ; null → "—"
const percent = (value: number | null) =>
  value === null ? '—' : `${value.toLocaleString('pt-BR', { maximumFractionDigits: 2 })}%`

// Comparativo: alunos do melhor para o pior
const studentsByAverage = computed(() =>
  [...(summary.value?.students ?? [])].sort(
    (a, b) => (b.average_percentage ?? -1) - (a.average_percentage ?? -1),
  ),
)

async function loadRanking(target = 1) {
  rankingLoading.value = true
  rankingError.value = null
  try {
    const result = await dashboardService.ranking(target, examFilter.value)
    ranking.value = result.data
    page.value = result.meta.current_page
    lastPage.value = result.meta.last_page
  } catch (e) {
    rankingError.value = (e as ApiError).message
  } finally {
    rankingLoading.value = false
  }
}

// Trocar o filtro sempre volta para a página 1
watch(examFilter, () => loadRanking(1))

onMounted(async () => {
  loadRanking(1)
  try {
    summary.value = await dashboardService.summary()
  } catch (e) {
    error.value = (e as ApiError).message
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <h1>Dashboard</h1>
  <p class="lead">Desempenho da turma nas provas ativas.</p>

  <p v-if="loading" class="muted">Carregando métricas...</p>
  <p v-else-if="error" class="alert alert-error">{{ error }}</p>

  <div v-else-if="summary && summary.overall.attempts_count === 0" class="card">
    <h2>Nenhuma prova respondida ainda</h2>
    <p class="muted">As métricas aparecem assim que os alunos enviarem as primeiras provas.</p>
  </div>

  <template v-else-if="summary">
    <div class="stats">
      <div class="stat stat-main">
        <span class="stat-label">Média da turma</span>
        <span class="stat-value">{{ percent(summary.overall.average_percentage) }}</span>
        <span class="stat-detail">de acertos, em todas as provas</span>
      </div>
      <div class="stat">
        <span class="stat-label">Melhor resultado</span>
        <span class="stat-value">{{ percent(summary.overall.best_percentage) }}</span>
        <span v-if="summary.best_attempt" class="stat-detail">
          {{ summary.best_attempt.student.name }} em {{ summary.best_attempt.exam.title }}
        </span>
      </div>
      <div class="stat">
        <span class="stat-label">Pior resultado</span>
        <span class="stat-value">{{ percent(summary.overall.worst_percentage) }}</span>
        <span v-if="summary.worst_attempt" class="stat-detail">
          {{ summary.worst_attempt.student.name }} em {{ summary.worst_attempt.exam.title }}
        </span>
      </div>
      <div class="stat">
        <span class="stat-label">Provas respondidas</span>
        <span class="stat-value">{{ summary.overall.attempts_count }}</span>
        <span class="stat-detail">tentativas enviadas</span>
      </div>
    </div>

    <section class="section">
      <h2>Por prova</h2>
      <div class="table-wrap">
        <table class="table">
          <thead>
            <tr>
              <th>Prova</th>
              <th class="num">Tentativas</th>
              <th class="num">Média</th>
              <th class="num">Melhor</th>
              <th class="num">Pior</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="exam in summary.exams" :key="exam.id">
              <td>{{ exam.title }}</td>
              <td class="num">{{ exam.attempts_count }}</td>
              <td class="num">{{ percent(exam.average_percentage) }}</td>
              <td class="num">{{ percent(exam.best_percentage) }}</td>
              <td class="num">{{ percent(exam.worst_percentage) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>

    <section class="section">
      <h2>Por aluno</h2>
      <div class="bars">
        <div v-for="student in studentsByAverage" :key="student.id" class="bar-row">
          <span class="bar-name">{{ student.name }}</span>
          <div class="bar-track" aria-hidden="true">
            <div class="bar-fill" :style="{ width: `${student.average_percentage ?? 0}%` }" />
          </div>
          <span class="bar-value">{{ percent(student.average_percentage) }}</span>
        </div>
      </div>
    </section>
  </template>

  <section v-if="!loading && !error" class="section">
    <div class="section-header">
      <h2>Ranking</h2>
      <select v-model="examFilter" class="input" aria-label="Filtrar ranking por prova">
        <option :value="null">Todas as provas</option>
        <option v-for="exam in summary?.exams ?? []" :key="exam.id" :value="exam.id">{{ exam.title }}</option>
      </select>
    </div>

    <p v-if="rankingError" class="alert alert-error">{{ rankingError }}</p>
    <p v-else-if="!rankingLoading && ranking.length === 0" class="muted">Nenhuma tentativa para mostrar.</p>

    <div v-else class="table-wrap">
      <table class="table">
        <thead>
          <tr>
            <th>Posição</th>
            <th>Aluno</th>
            <th>Prova</th>
            <th class="num">Acertos</th>
            <th class="num">Pontos</th>
            <th class="num">Percentual</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="entry in ranking" :key="entry.attempt_id">
            <td>
              <span class="rank" :class="{ 'rank-first': entry.position === 1 }">{{ entry.position }}</span>
            </td>
            <td>{{ entry.student.name }}</td>
            <td>{{ entry.exam.title }}</td>
            <td class="num">{{ entry.correct_count }} de {{ entry.total_questions }}</td>
            <td class="num">{{ entry.score }}</td>
            <td class="num">{{ percent(entry.percentage) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <PaginationBar :page="page" :last-page="lastPage" @change="loadRanking" />
  </section>
</template>