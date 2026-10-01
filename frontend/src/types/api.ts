export interface Resource<T> {
  data: T
}

export interface Student {
  id: number
  name: string
  email: string
}

export interface AttemptSummary {
  id: number
  score: number
  percentage: number
  submitted_at: string
}

export interface StudentExamSummary {
  id: number
  title: string
  description: string | null
  questions_count: number
  attempt: AttemptSummary | null
}

export interface StudentAlternative {
  id: number
  text: string
  position: number
}

export interface StudentQuestion {
  id: number
  statement: string
  points: number
  position: number
  alternatives: StudentAlternative[]
}

export interface StudentExam {
  id: number
  title: string
  description: string | null
  questions: StudentQuestion[]
}

export interface SubmitAnswer {
  question_id: number
  alternative_id: number | null
}

export interface AlternativeRef {
  id: number
  text: string
}

export interface AnswerResult {
  question_id: number
  statement: string
  chosen_alternative: AlternativeRef | null
  correct_alternative: AlternativeRef | null
  is_correct: boolean
}

export interface Attempt {
  id: number
  student?: { id: number; name: string }
  exam?: { id: number; title: string; is_deleted?: boolean }
  correct_count: number
  total_questions: number
  score: number
  percentage: number
  submitted_at: string
  answers?: AnswerResult[]
}

export interface Paginated<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export interface Alternative {
  id: number
  text: string
  is_correct: boolean
  position: number
}

export interface Question {
  id: number
  statement: string
  points: number
  position: number
  alternatives: Alternative[]
}

export interface Exam {
  id: number
  title: string
  description: string | null
  questions_count?: number
  attempts_count?: number
  questions?: Question[]
  created_at: string
}

export interface ExamPayload {
  title: string
  description: string | null
  questions?: {
    statement: string
    points: number
    alternatives: { text: string; is_correct: boolean }[]
  }[]
}

export interface DashboardAttempt {
  id: number
  student: { id: number; name: string }
  exam: { id: number; title: string }
  score: number
  percentage: number
}

export interface DashboardSummary {
  overall: {
    attempts_count: number
    average_percentage: number | null
    best_percentage: number | null
    worst_percentage: number | null
  }
  best_attempt: DashboardAttempt | null
  worst_attempt: DashboardAttempt | null
  exams: {
    id: number
    title: string
    attempts_count: number
    average_percentage: number | null
    best_percentage: number | null
    worst_percentage: number | null
  }[]
  students: {
    id: number
    name: string
    attempts_count: number
    average_percentage: number | null
  }[]
}

export interface RankingEntry {
  position: number
  attempt_id: number
  student: { id: number; name: string }
  exam: { id: number; title: string }
  score: number
  percentage: number
  correct_count: number
  total_questions: number
  submitted_at: string
}