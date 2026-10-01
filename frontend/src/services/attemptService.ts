import api from './api'
import type { Attempt, Resource, StudentExam, SubmitAnswer } from '@/types/api'

export const attemptService = {
  async exam(studentId: number, examId: number): Promise<StudentExam> {
    const { data } = await api.get<Resource<StudentExam>>(`/students/${studentId}/exams/${examId}`)
    return data.data
  },

  async submit(studentId: number, examId: number, answers: SubmitAnswer[]): Promise<Attempt> {
    const { data } = await api.post<Resource<Attempt>>(
      `/students/${studentId}/exams/${examId}/attempts`,
      { answers },
    )
    return data.data
  },

  async find(attemptId: number): Promise<Attempt> {
    const { data } = await api.get<Resource<Attempt>>(`/attempts/${attemptId}`)
    return data.data
  },
}