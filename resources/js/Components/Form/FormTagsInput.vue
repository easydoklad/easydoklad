<template>
  <TagsInput v-model="value" add-on-blur add-on-tab>
    <TagsInputItem v-for="item in value" :key="item" :value="item">
      <TagsInputItemText />
      <TagsInputItemDelete />
    </TagsInputItem>

    <TagsInputInput :placeholder="placeholder" />
  </TagsInput>
</template>

<script setup lang="ts">
import {
  TagsInput,
  TagsInputInput,
  TagsInputItem,
  TagsInputItemDelete,
  TagsInputItemText
} from '@/Components/TagsInput'
import { useVModel } from '@vueuse/core'

const emit = defineEmits(['update:modelValue'])

const props = defineProps<{
  modelValue?: Array<string> | undefined
  placeholder?: string
}>()

const value = useVModel(props, 'modelValue', emit)
</script>
