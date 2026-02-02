import {
  computed,
  type ComputedRef,
  inject,
  provide,
  reactive,
  toRaw,
  toRefs,
  watch
} from 'vue'
import sortBy from 'lodash/sortBy'

export { default as LocaleProvider } from './LocaleProvider.vue'
export { default as LocalizedInlineInput } from './LocalizedInlineInput.vue'
export { default as LocalizedTabInput } from './LocalizedTabInput.vue'

export type RawString = { value: string | null }
export type LocalizedString = Record<string, string | null>

export type TranslatableString = RawString | LocalizedString

export const LocalesInjectionKey = Symbol()

export interface LocalesContext {
  locale: ComputedRef<string>
  fallbackLocale: ComputedRef<string>
  locales: ComputedRef<Array<Locale>>
  required: ComputedRef<Array<string>>
}

export interface Locale {
  code: string
  name: string
}

export const injectLocales: () => LocalesContext = () => {
  return inject<LocalesContext>(LocalesInjectionKey, () => {
    throw new Error("Locales are not configured. Use LocaleProvider to configure available locales.")
  }, true)
}

export const provideLocales = (context: LocalesContext) => {
  provide(LocalesInjectionKey, context)
}

/**
 * Check whether translatable string is not localized.
 */
export function isRawString(value: TranslatableString): value is RawString {
  return (value as RawString).value !== undefined
}

/**
 * Check whether translatable string is localized.
 */
export function isLocalizedString(value: TranslatableString): value is LocalizedString {
  return (typeof value === 'object' && !('value' in value))
}

/**
 * Check whether two translatable strings are equal.
 */
export function areTranslatableStringsEqual(first: TranslatableString | null, second: TranslatableString | null): boolean {
  // If both are null, they are the same
  if (first === null && second === null) {
    return true
  }

  // If one is null and the other is not, they are different
  if (first === null || second === null) {
    return false
  }

  // Check if both are RawString
  if (isRawString(first) && isRawString(second)) {
    return first.value === second.value
  }

  // Check if both are LocalizedString
  if (isLocalizedString(first) && isLocalizedString(second)) {
    // Compare keys and values of both objects
    const firstKeys = Object.keys(first)
    const secondKeys = Object.keys(second)

    // Check if they have the same keys
    if (firstKeys.length !== secondKeys.length) {
      return false
    }

    return firstKeys.every((key) => (first as LocalizedString)[key] === (second as LocalizedString)[key])
  }

  // If one is RawString and the other is LocalizedString, they are different
  return false
}

/**
 * Get the string value of given translatable string.
 *
 * @param value The value of the string.
 * @param strict Whether to enforce strict localization rules.
 */
export function str(value: TranslatableString | string | null | undefined, strict: boolean = true): string|null {
  if (value === null || value === undefined) {
    return null
  }

  if (typeof value === 'string') {
    return value
  }

  if (isRawString(value)) {
    return value.value
  }

  const context = injectLocales()

  return strForLocale(value, context.locale.value, context.fallbackLocale.value, strict)
}

/**
 * Get the value of the string for given locale.
 *
 * If the value for the given locale is not localized, the fallback locale(s) will be used.
 * Value is considered localized when it is not empty or null. When strict mode is enabled,
 * a value is considered localized if it is present, regardless of whether it is empty or null.
 *
 * @param value The value of the string.
 * @param locale The locale key.
 * @param fallback The fallback locale key or an array of fallbacks.
 * @param strict Whether to enforce strict localization rules.
 */
export function strForLocale(value: LocalizedString, locale: string, fallback: Array<string> | string | null, strict: boolean): string|null {
  const rawValue = toRaw(value)

  if (locale in rawValue) {
    const localized = rawValue[locale]

    if (strict) {
      return localized
    }

    if (localized !== null && localized !== '') {
      return localized
    }
  }

  if (fallback === null) {
    return null
  }

  const fallbackLocales = Array.isArray(fallback) ? fallback : [fallback]

  for (const fallbackLocale of fallbackLocales) {
    if (fallbackLocale in rawValue) {
      const localized = rawValue[fallbackLocale]

      if (strict) {
        return localized
      }

      if (localized !== null && localized !== '') {
        return localized
      }
    }
  }

  return null
}

export interface TranslatableInputProps {
  modelValue?: TranslatableString | null
  disabled?: boolean
}

export interface TranslatableInputEmits {
  'update:modelValue': [value: TranslatableString | null]
}

export function useTranslatableInput(
  componentProps: TranslatableInputProps,
  emit: (event: keyof TranslatableInputEmits, ...args: any[]) => void
) {
  const props = toRefs(componentProps)

  const { locales: availableLocales, required: requiredLocales } = injectLocales()

  type LocalizedEntry = {
    locale: string
    value: string | undefined
  }

  type InternalState = {
    localized: boolean
    rawValue: string | undefined
    localizedValue: Array<LocalizedEntry>
  }

  const getLocaleName = (locale: string) => availableLocales.value.find(it => it.code === locale)?.name || locale

  const createEntriesFromLocalizedString: (value: LocalizedString) => Array<LocalizedEntry> = value => {
    const entries = Object.keys(value).reduce((acc, locale) => {
      acc.push({
        locale,
        value: value[locale] || undefined
      })

      return acc
    }, [] as Array<LocalizedEntry>)

    const sortedLocales = availableLocales.value.map(it => it.code)

    return sortBy(entries, (obj) => {
      const index = sortedLocales.indexOf(obj.locale)
      return index === -1 ? Infinity : index
    })
  }

  const createEmptyEntriesFromRequiredLocales: () => Array<LocalizedEntry> = () => requiredLocales.value.map(locale => ({
    locale,
    value: undefined
  }))

  const createStateFromTranslatableString: (value: TranslatableString | null) => InternalState = value => {
    return {
      localized: value ? isLocalizedString(value) : false,
      rawValue: value && isRawString(value) ? (value.value || undefined) : undefined,
      localizedValue: value && isLocalizedString(value)
        ? createEntriesFromLocalizedString(value)
        : createEmptyEntriesFromRequiredLocales()
    }
  }

  const state = reactive<{ value: InternalState }>({
    value: createStateFromTranslatableString(props.modelValue?.value || null)
  })

  const primaryLocale = computed(() => {
    if (requiredLocales.value.length > 0) {
      return requiredLocales.value[0]
    }

    return null
  })

  const createModelValue: () => TranslatableString | null = () => {
    if (state.value.localized) {
      return state.value.localizedValue.reduce((acc, val) => {
        acc[val.locale] = val.value || null

        return acc
      }, {} as LocalizedString)
    }

    if (state.value.rawValue) {
      return { value: state.value.rawValue }
    }

    return null
  }

  watch(state, () => {
    const currentModelValue = props.modelValue?.value || null
    const updatedModelValue = createModelValue()

    if (! areTranslatableStringsEqual(currentModelValue, updatedModelValue)) {
      emit('update:modelValue', updatedModelValue)
    }
  })

  const setPrimaryValue = () => {
    const primary = primaryLocale.value

    if (!primary) {
      return
    }

    const entry = state.value.localizedValue.find(it => it.locale === primary)

    if (!entry) {
      return
    }

    if (entry && entry.value !== state.value.rawValue) {
      if (state.value.localized) {
        entry.value = state.value.rawValue
      } else {
        state.value.rawValue = entry.value
      }
    }
  }

  watch(() => props.modelValue?.value, updatedModelValue => {
    if (!areTranslatableStringsEqual(updatedModelValue || null, createModelValue())) {
      state.value = createStateFromTranslatableString(updatedModelValue || null)
    }
  })

  const selectableLocales = computed(() => {
    const visibleLocales = state.value.localizedValue.map(it => it.locale)

    return availableLocales.value.filter(it => !visibleLocales.includes(it.code))
  })

  const addLocale = (locale: string) => {
    if (! state.value.localizedValue.find(it => it.locale === locale)) {
      state.value.localizedValue.push({
        locale,
        value: undefined,
      })
    }
  }

  const isRequired = (locale: string) => requiredLocales.value.includes(locale)

  const removeLocale = (locale: string) => {
    const idx = state.value.localizedValue.findIndex(it => it.locale === locale)
    if (idx >= 0) {
      state.value.localizedValue.splice(idx, 1)
    }
  }

  return {
    state,
    availableLocales: selectableLocales,
    setPrimaryValue,
    addLocale,
    removeLocale,
    isRequired,
    getLocaleName,
  }
}
