import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import categoryService from '@/services/categoryService'

export const useCategoryStore = defineStore('categories', () => {
  const categories = ref([])
  const loading = ref(false)

  const expenseCategories = computed(() =>
    categories.value.filter(c => c.type === 'expense' && Number(c.is_archived) === 0)
  )

  const incomeCategories = computed(() =>
    categories.value.filter(c => c.type === 'income' && Number(c.is_archived) === 0)
  )

  const archivedCategories = computed(() =>
    categories.value.filter(c => Number(c.is_archived) === 1)
  )

  async function fetchCategories(params = {}) {
    loading.value = true
    try {
      const { data } = await categoryService.getAll({ include_archived: 1, ...params })
      if (data.status === 'success') {
        categories.value = data.data
      }
    } finally {
      loading.value = false
    }
  }

  async function createCategory(catData) {
    const { data } = await categoryService.create(catData)
    if (data.status === 'success') {
      categories.value.push(data.data)
    }
    return data
  }

  async function updateCategory(id, catData) {
    const { data } = await categoryService.update(id, catData)
    if (data.status === 'success') {
      const idx = categories.value.findIndex(c => c.id === id)
      if (idx !== -1) categories.value[idx] = data.data
    }
    return data
  }

  async function toggleArchive(id) {
    const { data } = await categoryService.toggleArchive(id)
    if (data.status === 'success') {
      const idx = categories.value.findIndex(c => c.id === id)
      if (idx !== -1) categories.value[idx] = data.data
    }
    return data
  }

  async function deleteCategory(id) {
    const { data } = await categoryService.delete(id)
    if (data.status === 'success') {
      categories.value = categories.value.filter(c => c.id !== id)
    }
    return data
  }

  function getCategoryById(id) {
    return categories.value.find(c => c.id === id)
  }

  return {
    categories,
    loading,
    expenseCategories,
    incomeCategories,
    archivedCategories,
    fetchCategories,
    createCategory,
    updateCategory,
    toggleArchive,
    deleteCategory,
    getCategoryById,
  }
})
