import api from './api'

export default {
  getAll() {
    return api.get('/profiles')
  },

  create(data) {
    return api.post('/profiles', data)
  },

  update(id, data) {
    return api.put(`/profiles/${id}`, data)
  },

  setDefault(id) {
    return api.patch(`/profiles/${id}/default`)
  },

  delete(id) {
    return api.delete(`/profiles/${id}`)
  },
}
