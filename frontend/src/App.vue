<script setup lang="ts">
import { computed } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'

const route = useRoute()

const area = computed(() => {
  if (route.path.startsWith('/professor')) return 'professor'
  if (route.params.studentId) return 'aluno'
  return null
})

</script>

<template>
  <header class="topbar">
    <RouterLink to="/" class="brand">Provas Fênix</RouterLink>

    <nav v-if="area === 'professor'" class="nav">
      <RouterLink to="/professor/provas">Provas</RouterLink>
      <RouterLink to="/professor/dashboard">Dashboard</RouterLink>
    </nav>

    <nav v-else-if="area === 'aluno'" class="nav">
      <RouterLink :to="`/aluno/${route.params.studentId}/provas`">Minhas provas</RouterLink>
    </nav>

    <RouterLink v-if="area" to="/" class="switch">Trocar perfil</RouterLink>
  </header>

  <main class="container">
    <RouterView />
  </main>
</template>