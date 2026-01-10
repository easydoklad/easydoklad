<template>
  <Popover v-model:open="open">
    <PopoverTrigger as-child>
      <Button
        plain
        variant="outline"
        :disabled="disabled"
        :class="cn(
          'w-full justify-start text-left font-normal form-element',
          !date && 'text-muted-foreground',
          $attrs.class || ''
        )"
      >
        <CalendarIcon class="text-foreground h-4 w-4" />
        {{ date ? df.format(date.toDate(getLocalTimeZone())) : (placeholder || "Pick a date") }}
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-auto p-0" :to="to">
      <Calendar
        v-model="date"
        initial-focus
        locale="sk"
        @update:model-value="onSelected"
        :min-value="minValue"
        :max-value="maxValue"
      />
    </PopoverContent>
  </Popover>
</template>

<script setup lang="ts">
import { Button } from '@/Components/Button'
import { Calendar } from '@/Components/Calendar'
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/Popover'
import { cn } from '@/Utils'
import {
  DateFormatter,
  type DateValue,
  parseDate,
  getLocalTimeZone,
} from '@internationalized/date'
import { Calendar as CalendarIcon } from 'lucide-vue-next'
import { computed, ref, type Ref, watch } from 'vue'

const emit = defineEmits(['update:modelValue'])

const props = defineProps<{
  modelValue?: string | null | undefined
  placeholder?: string | null | undefined
  to?: string | HTMLElement
  closeOnSelect?: boolean
  disabled?: boolean
  min?: string | null | undefined
  max?: string | null | undefined
}>()

const df = new DateFormatter('sk-SK', {
  dateStyle: 'long',
})

const date = ref(props.modelValue ? parseDate(props.modelValue) : undefined) as Ref<DateValue | undefined>

const minValue = computed(() => props.min ? parseDate(props.min) : undefined)
const maxValue = computed(() => props.max ? parseDate(props.max) : undefined)

const open = ref(false)

watch(date, newDate => {
  const updatedValue = newDate?.toString() || undefined

  if (updatedValue !== props.modelValue) {
    emit('update:modelValue', updatedValue)
  }
})

watch(computed(() => props.modelValue), newModelValue => {
  const currentDate = date.value?.toString() || undefined
  const updatedDate = newModelValue || undefined

  if (currentDate !== updatedDate) {
    date.value = updatedDate ? (parseDate(updatedDate) as DateValue) : undefined
  }
})

const onSelected = () => {
  if (props.closeOnSelect) {
    open.value = false
  }
}
</script>
