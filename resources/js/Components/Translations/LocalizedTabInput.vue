<template>
  <div>
    <div v-if="state.value.localized">
      <Tabs v-model="visibleTab" :default-value="state.value.localizedValue.length > 0 ? state.value.localizedValue[0].locale : undefined">
        <div class="flex flex-row items-center gap-3">
          <TabsList>
            <div v-for="entry in state.value.localizedValue" class="relative">
              <TabsTrigger
                :value="entry.locale"
                :class="{ 'pr-7': disabled !== true && state.value.localizedValue.length > 1 && !isRequired(entry.locale) }"
              >
                {{ getLocaleName(entry.locale) }}
              </TabsTrigger>

              <Button v-if="disabled !== true && state.value.localizedValue.length > 1 && !isRequired(entry.locale)" @click.stop="onRemoveTranslation(entry.locale)" class="p-1 top-[5px] h-auto absolute right-1" variant="ghost-destructive">
                <XIcon class="size-3" />
              </Button>
            </div>
          </TabsList>

          <DropdownMenu v-if="state.value.localized && availableLocales.length > 0" v-model:open="open">
            <DropdownMenuTrigger as-child>
              <Button size="sm" variant="ghost" plain class="gap-1">
                <PlusIcon class="size-3" /> Pridať preklad
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="start" class="p-0">
              <Command>
                <CommandInput auto-focus placeholder="Hľadať jazyk…" />
                <CommandList>
                  <CommandEmpty>Jazyk nenájdený.</CommandEmpty>
                  <CommandGroup>
                    <CommandItem
                      v-for="locale in availableLocales"
                      @select="onAddTranslation(locale.code)"
                      :value="locale.name"
                    >{{ locale.name }}</CommandItem>
                  </CommandGroup>
                </CommandList>
              </Command>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>
        <TabsContent v-for="entry in state.value.localizedValue" :value="entry.locale">
            <slot v-bind="{ getValue: (): string | undefined => entry.value, setValue: (value: any) => entry.value = value }" />
        </TabsContent>
      </Tabs>
    </div>

    <div v-else class="w-full">
      <slot v-bind="{ getValue: (): string | undefined => state.value.rawValue, setValue: (value: any) => state.value.rawValue = value }" />
    </div>

    <div
      v-if="disabled !== true"
      :class="cn(
        'flex flex-row w-full',
        buttonSide === 'right' && 'justify-end',
      )">
      <div class="flex flex-row justify-between items-center h-8">
        <div class="inline-flex flex-row items-center gap-1.5">
          <Label for="localize" class="text-xs">Preložiť</Label>
          <Switch size="sm" id="localize" v-model="state.value.localized" @update:model-value="setPrimaryValue" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { cn } from '@/Utils'
import { reactiveOmit } from '@vueuse/core'
import { useTranslatableInput } from '.'
import type { TranslatableInputProps, TranslatableInputEmits } from '.'
import { Label } from '@/Components/Label'
import { Button } from '@/Components/Button'
import { Switch } from '@/Components/Switch'
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/Components/Tabs'
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList, } from '@/Components/Command'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger, } from '@/Components/DropdownMenu'
import { ref } from 'vue'
import { XIcon, PlusIcon } from 'lucide-vue-next'

const emits = defineEmits<TranslatableInputEmits>()
const props = defineProps<TranslatableInputProps & {
  buttonSide?: 'left' | 'right'
}>()

const open = ref(false)
const visibleTab = ref<string>()

const forwardedProps = reactiveOmit(props, 'buttonSide')

const {
  state,
  availableLocales,
  addLocale,
  removeLocale,
  isRequired,
  setPrimaryValue,
  getLocaleName,
} = useTranslatableInput(forwardedProps, emits)

const onAddTranslation = (locale: string) => {
  open.value = false

  addLocale(locale)
}

const onRemoveTranslation = (locale: string) => {
  removeLocale(locale)

  if (locale === visibleTab.value && state.value.localizedValue.length > 0) {
    visibleTab.value = state.value.localizedValue[0].locale
  }
}
</script>
