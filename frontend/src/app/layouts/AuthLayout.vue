<template>
  <div class="auth-layout">
    <div class="background-glows">
      <div class="glow glow-1"></div>
      <div class="glow glow-2"></div>
    </div>
    <router-view />
  </div>
</template>

<style scoped>
.auth-layout {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #0f172a;
  position: relative;
  overflow: hidden;
  padding: 1.5rem;
}

.background-glows {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 0;
}

.glow {
  position: absolute;
  border-radius: 50%;
  filter: blur(100px);
  opacity: 0.15;
}

.glow-1 {
  top: -10%;
  left: -10%;
  width: 50%;
  height: 50%;
  background: #ff416c;
}

.glow-2 {
  bottom: -10%;
  right: -10%;
  width: 50%;
  height: 50%;
  background: #ff4b2b;
}

:deep(.login-card) {
  position: relative;
  z-index: 1;
}
</style>
