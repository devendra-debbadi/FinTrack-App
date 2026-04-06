import api from './api'

export default {
  getAll(params = {}) {
    return api.get('/budgets', { params })
  },

  create(data) {
    return api.post('/budgets', data)
  },

  update(id, data) {
    return api.put(`/budgets/${id}`, data)
  },

  delete(id) {
    return api.delete(`/budgets/${id}`)
  },
}
