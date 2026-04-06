import api from './api'

export default {
  getByTransaction(transactionId) {
    return api.get(`/receipts/${transactionId}`)
  },

  upload(transactionId, file) {
    const formData = new FormData()
    formData.append('receipt', file)
    return api.post(`/receipts/${transactionId}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  },

  getDownloadUrl(receiptId) {
    return `/api/v1/receipts/download/${receiptId}`
  },

  delete(id) {
    return api.delete(`/receipts/${id}`)
  },
}
