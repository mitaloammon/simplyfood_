<script setup lang="ts">
const emit = defineEmits(['submit']);

const handleSubmit = (event: Event) => {
  event.preventDefault();
  emit('submit', event);
};
</script>

<template>
  <form class="base-form" @submit="handleSubmit">
    <slot></slot>
  </form>
</template>

<style scoped>
.base-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  width: 100%;
}
</style>
