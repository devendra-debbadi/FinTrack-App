import api from './api'

export default {
  getAll() {
    return api.get('/tags')
  },

  create(data) {
    return api.post('/tags', data)
  },

  update(id, data) {
    return api.put(`/tags/${id}`, data)
  },

  delete(id) {
    return api.delete(`/tags/${id}`)
  },
}
