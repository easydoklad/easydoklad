import { computed, type ComputedRef, inject, type MaybeRefOrGetter, provide, toValue } from "vue";

export { default as FormCombobox } from './FormCombobox.vue'
export { default as FormControl } from './FormControl.vue'
export { default as FormDescription } from './FormDescription.vue'
export { default as FormInlineError } from './FormInlineError.vue'
export { default as FormItem } from './FormItem.vue'
export { default as FormLabel } from './FormLabel.vue'
export { default as FormMessage } from './FormMessage.vue'
export { default as FormSelect } from './FormSelect.vue'
export { default as FormTagsInput } from './FormTagsInput.vue'

const FormControlContextKey = Symbol()

export interface FormControlContext {
  error: string | null | undefined
}

export function provideFormControlContext(context: MaybeRefOrGetter<FormControlContext>) {
  provide(FormControlContextKey, context);
}

export function injectFormControlContext(): ComputedRef<FormControlContext | null> {
  const value = inject<MaybeRefOrGetter>(FormControlContextKey)

  return computed(() => {
    if (value) {
      return toValue(value)
    }

    return null
  })
}
