<template>
  <router-view />
</template>

<style>
@import url('https://fonts.cdnfonts.com/css/optimistic-display');
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap');

:root {
  --font-primary: 'Optimistic Display', 'Outfit', 'Inter', 'Segoe UI', sans-serif;
}

html, body, #app {
  margin: 0;
  padding: 0;
  min-height: 100vh;
  background-color: #0f172a;
  color: #f8fafc;
  font-family: var(--font-primary);
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
</style>
