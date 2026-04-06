import api from './api'

export default {
  getAll(params = {}) {
    return api.get('/recurring', { params })
  },

  create(data) {
    return api.post('/recurring', data)
  },

  update(id, data) {
    return api.put(`/recurring/${id}`, data)
  },

  toggle(id) {
    return api.patch(`/recurring/${id}/toggle`)
  },

  process(id) {
    return api.post(`/recurring/${id}/process`)
  },

  delete(id) {
    return api.delete(`/recurring/${id}`)
  },
}
