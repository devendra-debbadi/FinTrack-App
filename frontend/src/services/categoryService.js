import api from './api'

export default {
  getAll(params = {}) {
    return api.get('/categories', { params })
  },

  create(data) {
    return api.post('/categories', data)
  },

  update(id, data) {
    return api.put(`/categories/${id}`, data)
  },

  toggleArchive(id) {
    return api.patch(`/categories/${id}/archive`)
  },

  delete(id) {
    return api.delete(`/categories/${id}`)
  },
}
