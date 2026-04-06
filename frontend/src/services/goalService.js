import api from './api'

export default {
  getAll(params = {}) {
    return api.get('/goals', { params })
  },

  getById(id) {
    return api.get(`/goals/${id}`)
  },

  create(data) {
    return api.post('/goals', data)
  },

  update(id, data) {
    return api.put(`/goals/${id}`, data)
  },

  deposit(id, data) {
    return api.post(`/goals/${id}/deposit`, data)
  },

  delete(id) {
    return api.delete(`/goals/${id}`)
  },
}
