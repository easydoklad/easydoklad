<template>
  <LocaleProvider :locales="locales" :required="requiredLocales" :locale="page.props.locale" :fallback-locale="page.props.fallbackLocale">
    <slot />

    <Toaster />
    <ConfirmationDialog />
  </LocaleProvider>
</template>

<script setup lang="ts">
import { Toaster } from '@/Components/Sonner'
import { ConfirmationDialog } from '@/Components/ConfirmationDialog'
import { LocaleProvider } from '@/Components/Translations'
import 'vue-sonner/style.css'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

defineOptions({
  inheritAttrs: false,
})

const page = usePage()
const locales = computed(() => page.props.locales)
const requiredLocales = computed(() => locales.value.filter(it => it.required).map(it => it.code))
</script>
