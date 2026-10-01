import api from './api'
import type { Attempt, Resource, Student, StudentExamSummary } from '@/types/api'

export const studentService = {
  async list(): Promise<Student[]> {
    const { data } = await api.get<Resource<Student[]>>('/students')
    return data.data
  },

    async exams(studentId: number): Promise<StudentExamSummary[]> {
    const { data } = await api.get<Resource<StudentExamSummary[]>>(`/students/${studentId}/exams`)
    return data.data
  },

  async attempts(studentId: number): Promise<Attempt[]> {
    const { data } = await api.get<Resource<Attempt[]>>(`/students/${studentId}/attempts`)
    return data.data
  },
}