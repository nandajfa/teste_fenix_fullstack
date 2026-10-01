<template>
  <section>
    <h1>Boas-vindas às Provas Fênix</h1>
    <p class="lead">Como você vai acessar?</p>

    <div class="choices">
      <RouterLink to="/professor/provas" class="choice">
        <svg class="choice-icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.5"
          stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <rect x="6" y="8" width="36" height="24" rx="2" />
          <path d="M12 16h14M12 23h20M18 32l-4 10M30 32l4 10" />
        </svg>
        <span class="choice-title">Sou professor</span>
        <span class="choice-text">Crie e edite provas e acompanhe o desempenho da turma.</span>
        <span class="btn btn-primary">Entrar como professor</span>
      </RouterLink>

      <RouterLink to="/aluno" class="choice">
        <svg class="choice-icon" viewBox="0 0 48 48" fill="none" stroke="currentColor" stroke-width="2.5"
          stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M18 12V9a6 6 0 0 1 12 0v3" />
          <rect x="10" y="12" width="28" height="30" rx="8" />
          <rect x="16" y="28" width="16" height="9" rx="3" />
          <path d="M16 21h16" />
        </svg>
        <span class="choice-title">Sou aluno</span>
        <span class="choice-text">Responda às suas provas e veja suas notas.</span>
        <span class="btn btn-primary">Entrar como aluno</span>
      </RouterLink>
    </div>
  </section>
</template>
