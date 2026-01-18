<template>
  <Switch v-model="form.value" @click="toggle" :disabled="disabled" />
</template>

<script setup lang="ts">
import { useForm } from '@inertiajs/vue3'
import { watch } from 'vue'
import Switch from './Switch.vue'

const props = withDefaults(defineProps<{
  value: boolean
  url: string
  field: string
  method?: 'post' | 'put' | 'patch'
  disabled?: boolean
}>(), {
  method: 'post'
})

const form = useForm(() => ({
  value: props.value,
}))

const toggle = () => {
  if (form.processing) {
    return
  }

  form
    .transform(it => ({ [props.field]: !it.value }))
    .submit(props.method, props.url, {
      preserveScroll: true,
      preserveState: true,
      showProgress: false,
      onFinish: () => {
        form.reset()
      },
    })
}

watch(() => props.value, value => {
  if (value !== form.value) {
    form.value = value
  }
})
</script>
