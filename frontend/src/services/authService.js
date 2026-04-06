import api from './api'

export default {
  login(email, password) {
    return api.post('/auth/login', { email, password })
  },

  register(name, email, password) {
    return api.post('/auth/register', { name, email, password })
  },

  logout() {
    return api.post('/auth/logout')
  },

  me() {
    return api.get('/auth/me')
  },

  updateProfile(name, email) {
    return api.put('/auth/profile', { name, email })
  },

  changePassword(currentPassword, newPassword) {
    return api.put('/auth/password', {
      current_password: currentPassword,
      new_password: newPassword,
    })
  },
}
