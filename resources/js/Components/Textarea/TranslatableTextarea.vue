<template>
  <LocalizedTabInput v-bind="forwarded" v-slot="{ setValue, getValue }">
    <Textarea
      :model-value="getValue()"
      @update:model-value="setValue"
      :rows="rows"
    />
  </LocalizedTabInput>
</template>

<script setup lang="ts">
import type { TranslatableInputEmits, TranslatableInputProps } from '@/Components/Translations'
import { reactiveOmit } from '@vueuse/core'
import { useForwardPropsEmits } from 'reka-ui'
import { LocalizedTabInput } from '@/Components/Translations'
import Textarea from './Textarea.vue'

const emits = defineEmits<TranslatableInputEmits>()
const props = defineProps<TranslatableInputProps & {
  rows?: number | string
}>()

const forwardedProps = reactiveOmit(props, 'rows')

const forwarded = useForwardPropsEmits(forwardedProps, emits)
</script>
