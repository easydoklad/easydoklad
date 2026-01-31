<template>
  <slot />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { type Locale, provideLocales } from '.'

const props = defineProps<{
  locales: Array<Locale>
  required: Array<string>
  locale: string
  fallbackLocale: string
}>()

provideLocales({
  locale: computed(() => props.locale),
  required: computed(() => props.required),
  locales: computed(() => props.locales),
  fallbackLocale: computed(() => props.fallbackLocale),
})
</script>
