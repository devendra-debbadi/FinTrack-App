import api from './api'

export default {
  getAll(params = {}) {
    return api.get('/net-worth', { params })
  },

  create(data) {
    return api.post('/net-worth', data)
  },

  update(id, data) {
    return api.put(`/net-worth/${id}`, data)
  },

  delete(id) {
    return api.delete(`/net-worth/${id}`)
  },
}
