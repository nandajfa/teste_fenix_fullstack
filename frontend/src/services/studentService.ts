import api from './api'
import type { Resource, Student } from '@/types/api'

export const studentService = {
  async list(): Promise<Student[]> {
    const { data } = await api.get<Resource<Student[]>>('/students')
    return data.data
  },
}