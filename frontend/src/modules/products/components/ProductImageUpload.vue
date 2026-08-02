<script setup lang="ts">
import { onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps<{
  modelValue: File | null;
  errorMessage?: string;
}>();

const emit = defineEmits<{
  (event: 'update:modelValue', value: File | null): void;
}>();

const imagePreview = ref<string | null>(null);

const revokePreview = () => {
  if (imagePreview.value) {
    URL.revokeObjectURL(imagePreview.value);
    imagePreview.value = null;
  }
};

const onImageChange = (event: Event) => {
  const target = event.target as HTMLInputElement;
  const selectedFile = target.files?.[0] || null;
  emit('update:modelValue', selectedFile);
};

watch(
  () => props.modelValue,
  (file) => {
    revokePreview();

    if (!file) {
      return;
    }

    imagePreview.value = URL.createObjectURL(file);
  },
  { immediate: true }
);

onBeforeUnmount(() => {
  revokePreview();
});
</script>

<template>
  <label class="field">
    <span class="label-text">Imagem</span>
    <input class="control" type="file" accept="image/png,image/jpeg,image/webp" @change="onImageChange" />
    <span v-if="errorMessage" class="error-text">{{ errorMessage }}</span>
    <img v-if="imagePreview" :src="imagePreview" alt="Pré-visualização do produto" class="preview" />
  </label>
</template>

<style scoped>
.field {
  display: flex;
  flex-direction: column;
  gap: 0.45rem;
  color: #cbd5e1;
  font-size: 0.85rem;
}

.label-text {
  font-weight: 500;
}

.control {
  width: 100%;
  border: 1px solid rgba(255, 255, 255, 0.14);
  border-radius: 10px;
  background: rgba(15, 23, 42, 0.65);
  color: #f8fafc;
  padding: 0.65rem 0.75rem;
  box-sizing: border-box;
}

.error-text {
  color: #fda4af;
  font-size: 0.75rem;
}

.preview {
  margin-top: 0.35rem;
  height: 8rem;
  width: 8rem;
  border-radius: 0.75rem;
  border: 1px solid rgba(255, 255, 255, 0.14);
  object-fit: cover;
}
</style>
