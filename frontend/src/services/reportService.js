import api from './api'

export default {
  monthly(params = {}) {
    return api.get('/reports/monthly', { params })
  },

  yearly(params = {}) {
    return api.get('/reports/yearly', { params })
  },

  categoryDetail(categoryId, params = {}) {
    return api.get(`/reports/category/${categoryId}`, { params })
  },

  incomeVsExpense(params = {}) {
    return api.get('/reports/income-vs-expense', { params })
  },

  budgetPerformance(params = {}) {
    return api.get('/reports/budget-performance', { params })
  },

  exportCsv(params = {}) {
    return api.get('/reports/export/csv', {
      params,
      responseType: 'blob',
    })
  },

  importCsv(file) {
    const formData = new FormData()
    formData.append('file', file)
    return api.post('/reports/import/csv', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  },
}
