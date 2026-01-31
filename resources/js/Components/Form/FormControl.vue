<template>
  <div v-if="variant === 'horizontal'" class="form-control w-full flex flex-col sm:flex-row" :class="{ 'has-error': !!error }">
    <div class="flex flex-col space-y-2 sm:w-2/5 pb-2 sm:pb-0 sm:pr-4" :class="{ 'sm:pt-2.5': !help }">
      <FormLabel v-if="label" :for="props.for || undefined" :error="props.error || ''">{{ label }} <span v-if="required" class="text-destructive">*</span></FormLabel>
      <FormDescription class="hidden sm:block" v-if="help">{{ help }}</FormDescription>
    </div>
    <div class="flex flex-col sm:w-3/5">
      <slot />
      <FormMessage v-if="error" :message="error" class="mt-2" />
      <FormDescription class="sm:hidden mt-2" v-if="help">{{ help }}</FormDescription>
    </div>
  </div>

  <FormItem class="form-control" v-else :class="{ 'has-error': !!error }">
    <div v-if="label" class="inline-flex flex-row">
      <FormLabel :for="props.for || undefined" :error="props.error || ''">{{ label }} <span v-if="required" class="text-destructive">*</span></FormLabel>
      <TooltipProvider v-if="help && helpVariant === 'tooltip'">
        <Tooltip>
          <TooltipTrigger>
            <InfoIcon class="size-4 text-muted-foreground ml-2" />
          </TooltipTrigger>
          <TooltipContent class="max-w-sm">
            {{ help }}
          </TooltipContent>
        </Tooltip>
      </TooltipProvider>
    </div>
    <div class="w-full">
      <slot />
    </div>
    <FormDescription v-if="help && helpVariant === 'inline'">{{ help }}</FormDescription>
    <FormMessage v-if="error && hideError !== true" :message="error" />
  </FormItem>
</template>

<script setup lang="ts">
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/Tooltip'
import { computed } from "vue";
import {
  FormItem,
  FormLabel,
  FormMessage,
  FormDescription,
  type FormControlContext,
  provideFormControlContext
} from "./";
import { InfoIcon } from 'lucide-vue-next'

const props = withDefaults(defineProps<{
  variant?: 'vertical' | 'horizontal'
  for?: string
  label?: string | null | undefined
  help?: string | null | undefined
  error?: string | null | undefined
  required?: boolean
  hideError?: boolean
  helpVariant?: 'inline' | 'tooltip'
}>(), {
  variant: 'vertical',
  helpVariant: 'inline',
})

const context = computed<FormControlContext>(() => ({
  error: props.error,
}))

provideFormControlContext(context)
</script>
