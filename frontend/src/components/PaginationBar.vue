<script setup lang="ts">
defineProps<{ page: number; lastPage: number }>()
const emit = defineEmits<{ change: [page: number] }>()
</script>

<template>
  <nav v-if="lastPage > 1" class="pagination" aria-label="Paginação">
    <button type="button" class="btn" :disabled="page <= 1" @click="emit('change', page - 1)">Anterior</button>
    <span class="muted">Página {{ page }} de {{ lastPage }}</span>
    <button type="button" class="btn" :disabled="page >= lastPage" @click="emit('change', page + 1)">
      Próxima
    </button>
  </nav>
</template>