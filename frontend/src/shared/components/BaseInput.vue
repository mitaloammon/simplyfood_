<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
  defineProps<{
    modelValue?: string | number;
    type?: string;
    label?: string;
    placeholder?: string;
    error?: string;
    disabled?: boolean;
    id?: string;
  }>(),
  {
    type: 'text',
    disabled: false,
  }
);

const emit = defineEmits(['update:modelValue', 'blur', 'focus']);

const inputId = computed(() => props.id || `input-${Math.random().toString(36).substring(2, 9)}`);

const handleInput = (event: Event) => {
  const target = event.target as HTMLInputElement;
  emit('update:modelValue', target.value);
};
</script>

<template>
  <div class="input-group" :class="{ 'has-error': error, 'is-disabled': disabled }">
    <label v-if="label" :for="inputId" class="input-label">{{ label }}</label>
    <div class="input-wrapper">
      <input
        :id="inputId"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        class="input-control"
        @input="handleInput"
        @blur="emit('blur', $event)"
        @focus="emit('focus', $event)"
      />
    </div>
    <span v-if="error" class="input-error-message">{{ error }}</span>
  </div>
</template>

<style scoped>
.input-group {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  width: 100%;
}

.input-label {
  font-family: 'Outfit', 'Inter', sans-serif;
  font-size: 0.875rem;
  font-weight: 500;
  color: #cbd5e0;
  transition: color 0.3s ease;
}

.input-wrapper {
  position: relative;
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.03);
  border: 1px solid rgba(255, 255, 255, 0.1);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.input-wrapper:focus-within {
  border-color: #ff4b2b;
  box-shadow: 0 0 0 3px rgba(255, 75, 43, 0.15);
  background: rgba(255, 255, 255, 0.06);
}

.input-control {
  width: 100%;
  padding: 0.75rem 1rem;
  font-family: 'Inter', sans-serif;
  font-size: 0.95rem;
  background: transparent;
  border: none;
  color: #ffffff;
  outline: none;
}

.input-control::placeholder {
  color: #718096;
}

/* Error States */
.has-error .input-label {
  color: #fc8181;
}

.has-error .input-wrapper {
  border-color: #e53e3e;
  box-shadow: 0 0 0 3px rgba(229, 62, 62, 0.15);
}

.input-error-message {
  font-size: 0.8rem;
  color: #fc8181;
  font-family: 'Inter', sans-serif;
  margin-top: 0.1rem;
}

/* Disabled State */
.is-disabled {
  opacity: 0.5;
}
.is-disabled .input-control {
  cursor: not-allowed;
}
</style>
