<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { attemptService } from '@/services/attemptService'
import type { ApiError } from '@/services/api'
import type { Attempt } from '@/types/api'

const props = defineProps<{ studentId: string; attemptId: string }>()

const attempt = ref<Attempt | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)

onMounted(async () => {
  try {
    attempt.value = await attemptService.find(Number(props.attemptId))
  } catch (e) {
    error.value = (e as ApiError).message
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <p v-if="loading" class="muted">Carregando resultado...</p>
  <p v-else-if="!attempt" class="alert alert-error">{{ error }}</p>

  <template v-else>
    <RouterLink :to="`/aluno/${studentId}/provas`" class="back-link">Minhas provas</RouterLink>

    <section class="result-hero">
      <div class="result-percentage">{{ attempt.percentage }}%</div>
      <div>
        <h1>{{ attempt.exam?.title }}</h1>
        <p>
          {{ attempt.correct_count }} de {{ attempt.total_questions }} questões certas ·
          {{ attempt.score }} pontos
        </p>
      </div>
    </section>

    <article
      v-for="(answer, index) in attempt.answers"
      :key="answer.question_id"
      class="card question answer"
      :class="answer.is_correct ? 'is-correct' : 'is-wrong'"
    >
      <div class="question-head">
        <span class="question-number">Questão {{ index + 1 }}</span>
        <span class="answer-status">{{ answer.is_correct ? 'Certa' : 'Errada' }}</span>
      </div>
      <p class="question-statement">{{ answer.statement }}</p>
      <dl>
        <dt>Sua resposta</dt>
        <dd>{{ answer.chosen_alternative?.text ?? 'Em branco' }}</dd>
        <template v-if="!answer.is_correct">
          <dt>Resposta certa</dt>
          <dd>{{ answer.correct_alternative?.text }}</dd>
        </template>
      </dl>
    </article>
  </template>
</template>
