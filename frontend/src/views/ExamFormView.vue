<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { examService } from '@/services/examService'
import type { ApiError } from '@/services/api'
import type { ExamPayload } from '@/types/api'

interface AlternativeForm {
  text: string
}

interface QuestionForm {
  statement: string
  points: number
  correct: number // índice da alternativa correta
  alternatives: AlternativeForm[]
}

const MIN_ALTERNATIVES = 2
const MAX_ALTERNATIVES = 6

const props = defineProps<{ id?: string }>()
const router = useRouter()
const isEdit = computed(() => props.id !== undefined)

const title = ref('')
const description = ref('')
const questions = ref<QuestionForm[]>([])
const locked = ref(false) // prova já respondida: questões não podem mudar

const loading = ref(isEdit.value)
const saving = ref(false)
const error = ref<string | null>(null)
const fieldErrors = ref<Record<string, string[]>>({})

const newQuestion = (): QuestionForm => ({
  statement: '',
  points: 1,
  correct: 0,
  alternatives: [{ text: '' }, { text: '' }, { text: '' }, { text: '' }],
})

const letter = (index: number) => String.fromCharCode(65 + index)
const errorFor = (key: string) => fieldErrors.value[key]?.[0]

function addQuestion() {
  questions.value.push(newQuestion())
}

function removeQuestion(index: number) {
  questions.value.splice(index, 1)
}

function addAlternative(question: QuestionForm) {
  question.alternatives.push({ text: '' })
}

function removeAlternative(question: QuestionForm, index: number) {
  question.alternatives.splice(index, 1)
  // Mantém a marcação da correta apontando para a mesma alternativa
  if (question.correct === index) question.correct = 0
  else if (question.correct > index) question.correct--
}

function buildPayload(): ExamPayload {
  const payload: ExamPayload = {
    title: title.value,
    description: description.value || null,
  }

  if (!locked.value) {
    payload.questions = questions.value.map((question) => ({
      statement: question.statement,
      points: question.points,
      alternatives: question.alternatives.map((alternative, index) => ({
        text: alternative.text,
        is_correct: index === question.correct,
      })),
    }))
  }

  return payload
}

async function save() {
  saving.value = true
  error.value = null
  fieldErrors.value = {}

  try {
    if (isEdit.value) {
      await examService.update(Number(props.id), buildPayload())
    } else {
      await examService.create(buildPayload())
    }
    router.push({ path: '/professor/provas', query: { saved: '1' } })
  } catch (e) {
    const apiError = e as ApiError
    fieldErrors.value = apiError.errors
    error.value = apiError.status === 422 ? 'Revise os campos destacados.' : apiError.message
    saving.value = false
  }
}

onMounted(async () => {
  if (!isEdit.value) {
    questions.value = [newQuestion()]
    return
  }

  try {
    const exam = await examService.find(Number(props.id))
    title.value = exam.title
    description.value = exam.description ?? ''
    locked.value = (exam.attempts_count ?? 0) > 0
    questions.value = (exam.questions ?? []).map((question) => ({
      statement: question.statement,
      points: Number(question.points),
      correct: Math.max(0, question.alternatives.findIndex((alternative) => alternative.is_correct)),
      alternatives: question.alternatives.map((alternative) => ({ text: alternative.text })),
    }))
  } catch (e) {
    error.value = (e as ApiError).message
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <RouterLink to="/professor/provas" class="back-link">Voltar para as provas</RouterLink>
  <h1>{{ isEdit ? 'Editar prova' : 'Nova prova' }}</h1>

  <p v-if="loading" class="muted">Carregando prova...</p>

  <form v-else class="form" @submit.prevent="save">
    <p v-if="error" class="alert alert-error">{{ error }}</p>
    <p v-if="locked" class="alert alert-info">
      Esta prova já foi respondida. Você pode alterar o título e a descrição, mas não as questões, para não mudar
      as notas dos alunos.
    </p>

    <div class="card form">
      <div class="field">
        <label for="title">Título</label>
        <input id="title" v-model="title" class="input" :class="{ 'has-error': errorFor('title') }" maxlength="255" />
        <span v-if="errorFor('title')" class="field-error">{{ errorFor('title') }}</span>
      </div>
      <div class="field">
        <label for="description">Descrição (opcional)</label>
        <textarea id="description" v-model="description" class="input" />
      </div>
    </div>

    <span v-if="errorFor('questions')" class="field-error">{{ errorFor('questions') }}</span>

    <div v-for="(question, qIndex) in questions" :key="qIndex" class="card question-form">
      <div class="question-head">
        <span class="question-number">Questão {{ qIndex + 1 }}</span>
        <button
          v-if="!locked && questions.length > 1"
          type="button"
          class="btn btn-small btn-ghost btn-danger"
          @click="removeQuestion(qIndex)"
        >
          Remover questão
        </button>
      </div>

      <fieldset :disabled="locked">
        <div class="question-grid">
          <div class="field">
            <label :for="`statement-${qIndex}`">Enunciado</label>
            <textarea
              :id="`statement-${qIndex}`"
              v-model="question.statement"
              class="input"
              :class="{ 'has-error': errorFor(`questions.${qIndex}.statement`) }"
            />
            <span v-if="errorFor(`questions.${qIndex}.statement`)" class="field-error">
              {{ errorFor(`questions.${qIndex}.statement`) }}
            </span>
          </div>
          <div class="field">
            <label :for="`points-${qIndex}`">Pontos</label>
            <input
              :id="`points-${qIndex}`"
              v-model.number="question.points"
              type="number"
              min="0.01"
              max="999.99"
              step="0.01"
              class="input"
            />
          </div>
        </div>

        <div class="field">
          <span class="field-label">Alternativas</span>
          <span class="field-hint">Marque a alternativa correta.</span>

          <div v-for="(alternative, aIndex) in question.alternatives" :key="aIndex" class="alternative-row">
            <input
              v-model="question.correct"
              type="radio"
              :name="`correct-${qIndex}`"
              :value="aIndex"
              :aria-label="`Alternativa ${letter(aIndex)} é a correta`"
            />
            <span class="option-letter">{{ letter(aIndex) }}</span>
            <input
              v-model="alternative.text"
              class="input"
              :class="{ 'has-error': errorFor(`questions.${qIndex}.alternatives.${aIndex}.text`) }"
              :aria-label="`Texto da alternativa ${letter(aIndex)}`"
            />
            <button
              v-if="question.alternatives.length > MIN_ALTERNATIVES"
              type="button"
              class="btn btn-small btn-ghost"
              :aria-label="`Remover alternativa ${letter(aIndex)}`"
              @click="removeAlternative(question, aIndex)"
            >
              Remover
            </button>
          </div>

          <span v-if="errorFor(`questions.${qIndex}.alternatives`)" class="field-error">
            {{ errorFor(`questions.${qIndex}.alternatives`) }}
          </span>

          <button
            v-if="question.alternatives.length < MAX_ALTERNATIVES"
            type="button"
            class="btn btn-small"
            @click="addAlternative(question)"
          >
            Adicionar alternativa
          </button>
        </div>
      </fieldset>
    </div>

    <div v-if="!locked">
      <button type="button" class="btn" @click="addQuestion">Adicionar questão</button>
    </div>

    <div class="submit-bar">
      <RouterLink to="/professor/provas" class="btn btn-ghost">Cancelar</RouterLink>
      <button type="submit" class="btn btn-primary" :disabled="saving">
        {{ saving ? 'Salvando...' : 'Salvar prova' }}
      </button>
    </div>
  </form>
</template>
