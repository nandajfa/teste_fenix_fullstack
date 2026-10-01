<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { attemptService } from '@/services/attemptService'
import type { ApiError } from '@/services/api'
import type { StudentExam } from '@/types/api'

const props = defineProps<{ studentId: string; examId: string }>()
const studentId = Number(props.studentId)
const examId = Number(props.examId)
const router = useRouter()

const exam = ref<StudentExam | null>(null)
const answers = ref<Record<number, number>>({}) // question_id → alternative_id
const loading = ref(true)
const submitting = ref(false)
const error = ref<string | null>(null)

const total = computed(() => exam.value?.questions.length ?? 0)
const answered = computed(() => Object.keys(answers.value).length)

const letter = (index: number) => String.fromCharCode(65 + index) // 0 → A

onMounted(async () => {
  try {
    exam.value = await attemptService.exam(studentId, examId)
  } catch (e) {
    error.value = (e as ApiError).message
  } finally {
    loading.value = false
  }
})

async function submit() {
  if (!exam.value) return

  const blank = total.value - answered.value
  if (blank > 0 && !confirm(`Você deixou ${blank} questão(ões) em branco. Enviar mesmo assim?`)) {
    return
  }

  submitting.value = true
  error.value = null

  try {
    const attempt = await attemptService.submit(
      studentId,
      examId,
      exam.value.questions.map((question) => ({
        question_id: question.id,
        alternative_id: answers.value[question.id] ?? null,
      })),
    )
    router.push(`/aluno/${studentId}/resultados/${attempt.id}`)
  } catch (e) {
    error.value = (e as ApiError).message
    submitting.value = false
  }
}
</script>

<template>
  <p v-if="loading" class="muted">Carregando prova...</p>
  <p v-else-if="!exam" class="alert alert-error">{{ error }}</p>

  <form v-else @submit.prevent="submit">
    <RouterLink :to="`/aluno/${studentId}/provas`" class="back-link">Voltar</RouterLink>
    <h1>{{ exam.title }}</h1>
    <p v-if="exam.description" class="lead">{{ exam.description }}</p>

    <fieldset v-for="(question, qIndex) in exam.questions" :key="question.id" class="card question">
      <div class="question-head">
        <span class="question-number">Questão {{ qIndex + 1 }}</span>
        <span class="muted">{{ question.points }} {{ question.points === 1 ? 'ponto' : 'pontos' }}</span>
      </div>
      <legend class="question-statement">{{ question.statement }}</legend>

      <div class="options">
        <label v-for="(alternative, aIndex) in question.alternatives" :key="alternative.id" class="option">
          <input
            v-model="answers[question.id]"
            type="radio"
            :name="`question-${question.id}`"
            :value="alternative.id"
          />
          <span class="option-letter">{{ letter(aIndex) }}</span>
          <span>{{ alternative.text }}</span>
        </label>
      </div>
    </fieldset>

    <p v-if="error" class="alert alert-error">{{ error }}</p>

    <div class="submit-bar">
      <span class="muted">{{ answered }} de {{ total }} respondidas</span>
      <button type="submit" class="btn btn-primary" :disabled="submitting">
        {{ submitting ? 'Enviando...' : 'Enviar prova' }}
      </button>
    </div>
  </form>
</template>
