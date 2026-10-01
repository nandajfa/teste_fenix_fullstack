import api from './api'
import type { DashboardSummary, Paginated, RankingEntry, Resource } from '@/types/api'

export const dashboardService = {
  async summary(): Promise<DashboardSummary> {
    const { data } = await api.get<Resource<DashboardSummary>>('/dashboard')
    return data.data
  },

  async ranking(page = 1, examId: number | null = null): Promise<Paginated<RankingEntry>> {
    const { data } = await api.get<Paginated<RankingEntry>>('/dashboard/ranking', {
      params: { page, per_page: 10, exam_id: examId ?? undefined },
    })
    return data
  },
}