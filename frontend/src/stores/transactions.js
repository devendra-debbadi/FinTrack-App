import { defineStore } from 'pinia'
import { ref, reactive } from 'vue'
import transactionService from '@/services/transactionService'

export const useTransactionStore = defineStore('transactions', () => {
  const transactions = ref([])
  const loading = ref(false)
  const pagination = reactive({
    total: 0,
    page: 1,
    per_page: 20,
    total_pages: 0,
  })

  const filters = reactive({
    type: null,
    category_id: null,
    date_from: null,
    date_to: null,
    search: '',
    min_amount: null,
    max_amount: null,
    sort: 'transaction_date',
    direction: 'DESC',
  })

  async function fetchTransactions(page = 1) {
    loading.value = true
    try {
      const params = { page, per_page: pagination.per_page }

      // Add active filters
      Object.entries(filters).forEach(([key, val]) => {
        if (val !== null && val !== '' && val !== undefined) {
          params[key] = val
        }
      })

      const { data } = await transactionService.getAll(params)
      if (data.status === 'success') {
        transactions.value = data.data.data
        pagination.total = data.data.total
        pagination.page = data.data.page
        pagination.total_pages = data.data.total_pages
      }
    } finally {
      loading.value = false
    }
  }

  async function createTransaction(txnData) {
    const { data } = await transactionService.create(txnData)
    if (data.status === 'success') {
      await fetchTransactions(pagination.page)
    }
    return data
  }

  async function updateTransaction(id, txnData) {
    const { data } = await transactionService.update(id, txnData)
    if (data.status === 'success') {
      await fetchTransactions(pagination.page)
    }
    return data
  }

  async function deleteTransaction(id) {
    const { data } = await transactionService.delete(id)
    if (data.status === 'success') {
      await fetchTransactions(pagination.page)
    }
    return data
  }

  function resetFilters() {
    filters.type = null
    filters.category_id = null
    filters.date_from = null
    filters.date_to = null
    filters.search = ''
    filters.min_amount = null
    filters.max_amount = null
    filters.sort = 'transaction_date'
    filters.direction = 'DESC'
  }

  return {
    transactions,
    loading,
    pagination,
    filters,
    fetchTransactions,
    createTransaction,
    updateTransaction,
    deleteTransaction,
    resetFilters,
  }
})
