<template>
  <LocalizedInlineInput v-bind="forwarded" v-slot="{ disabled, localized, setValue, getValue }">
    <Input
      :class="localized ? 'border-0 shadow-none rounded-none focus-visible:ring-0' : 'w-full'"
      :disabled="disabled"
      :model-value="getValue()"
      @update:model-value="setValue"
    />
  </LocalizedInlineInput>
</template>

<script setup lang="ts">
import type { TranslatableInputEmits, TranslatableInputProps } from '@/Components/Translations'
import { useForwardPropsEmits } from 'reka-ui'
import { LocalizedInlineInput } from '@/Components/Translations'
import Input from './Input.vue'

const emits = defineEmits<TranslatableInputEmits>()
const props = defineProps<TranslatableInputProps>()

const forwarded = useForwardPropsEmits(props, emits)
</script>
