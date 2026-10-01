import api from './api'
import type { Exam, ExamPayload, Paginated, Resource } from '@/types/api'

export const examService = {
  async list(page = 1): Promise<Paginated<Exam>> {
    const { data } = await api.get<Paginated<Exam>>('/exams', { params: { page } })
    return data
  },

  async find(id: number): Promise<Exam> {
    const { data } = await api.get<Resource<Exam>>(`/exams/${id}`)
    return data.data
  },

  async create(payload: ExamPayload): Promise<Exam> {
    const { data } = await api.post<Resource<Exam>>('/exams', payload)
    return data.data
  },

  async update(id: number, payload: ExamPayload): Promise<Exam> {
    const { data } = await api.put<Resource<Exam>>(`/exams/${id}`, payload)
    return data.data
  },

  async remove(id: number): Promise<void> {
    await api.delete(`/exams/${id}`)
  },
}