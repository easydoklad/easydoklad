<template>
  <component v-if="flagComponent" :is="flagComponent" />
  <Unknown v-else />
</template>

<script setup lang="ts">
import Unknown from './Unknown.vue'
import { LocaleCountries } from '.'
import { computed, type DefineComponent } from 'vue'

const Flags = import.meta.glob<DefineComponent>('./Flags/**.vue', { eager: true })

const props = defineProps<{
  country?: string
  locale?: string
}>()

const resolvedCountry = computed(() => {
  if (props.country) {
    return props.country
  }

  if (props.locale) {
    return LocaleCountries[props.locale] ? LocaleCountries[props.locale] : props.locale
  }

  return undefined
})

const flagComponent = computed(() => {
  const country = resolvedCountry.value

  if (!country) {
    return undefined
  }

  const path = Object.keys(Flags).find(path => path.endsWith(`/${country}.vue`))

  if (path) {
    return Flags[path].default
  }

  return undefined
})
</script>
