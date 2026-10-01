import axios, { AxiosError } from 'axios'

export interface ApiError {
  status: number
  message: string
  errors: Record<string, string[]>
}

const api = axios.create({
  baseURL: '/api',
  headers: { Accept: 'application/json' },
})

api.interceptors.response.use(
  (response) => response,
  (error: AxiosError<{ message?: string; errors?: Record<string, string[]> }>) => {
    const apiError: ApiError = {
      status: error.response?.status ?? 0,
      message: error.response?.data?.message ?? 'Não foi possível conectar à API.',
      errors: error.response?.data?.errors ?? {},
    }
    return Promise.reject(apiError)
  },
)

export default api