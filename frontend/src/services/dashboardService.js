import api from './api'

export default {
  getAll(period = 'month') {
    return api.get('/dashboard', { params: { period } })
  },

  getKpis(period = 'month') {
    return api.get('/dashboard/kpis', { params: { period } })
  },

  getTrend() {
    return api.get('/dashboard/trend')
  },

  getHeatmap() {
    return api.get('/dashboard/heatmap')
  },

  getInsights(period = 'month') {
    return api.get('/dashboard/insights', { params: { period } })
  },
}
