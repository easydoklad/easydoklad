<template>
  <div>
    <TooltipProvider>
      <div v-if="state.value.localized" :class="{ 'h-9': state.value.localizedValue.length == 1 }" class="overflow-hidden border divide-y divide-input rounded-md border-input shadow-sm transition-[color,box-shadow] focus-within:border-ring focus-within:ring-ring/50 focus-within:ring-[3px]">
        <div v-for="entry in state.value.localizedValue" class="flex flex-row relative">
          <Tooltip>
            <TooltipTrigger type="button">
              <div class="px-2 border-r border-input shrink-0 text-sm font-medium uppercase flex items-center justify-center">
                <Flag class="size-6" :locale="entry.locale" />
              </div>
            </TooltipTrigger>
            <TooltipContent side="left">
              {{ getLocaleName(entry.locale) }}
            </TooltipContent>
          </Tooltip>

          <slot v-bind="{ localized: true, disabled, getValue: (): string | undefined => entry.value, setValue: (value: any) => entry.value = value }" />

          <Button v-if="disabled !== true && state.value.localizedValue.length > 1 && !isRequired(entry.locale)" @click.prevent.stop="removeLocale(entry.locale)" size="sm" class="absolute right-[5px] top-[5px] p-1 h-auto text-muted-foreground" variant="ghost-destructive">
            <XIcon class="w-4 h-4" />
          </Button>
        </div>
      </div>

      <slot v-else v-bind="{ localized: false, disabled, getValue: (): string | undefined => state.value.rawValue, setValue: (value: any) => state.value.rawValue = value }" />

      <div v-if="disabled !== true" class="flex flex-row justify-between items-center mt-1 h-8">
        <div class="inline-flex flex-row items-center gap-2">
          <Label class="text-xs">Preložiť</Label>
          <Switch size="sm" v-model="state.value.localized" @update:model-value="setPrimaryValue" />
        </div>

        <DropdownMenu v-if="state.value.localized && availableLocales.length > 0" v-model:open="open">
          <DropdownMenuTrigger as-child>
            <Button variant="link" size="sm" class="text-xs px-0">Pridať preklad</Button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" class="p-0">
            <Command>
              <CommandInput auto-focus placeholder="Hľadať jazyk…" />
              <CommandList>
                <CommandEmpty>Jazyk nenájdený.</CommandEmpty>
                <CommandGroup>
                  <CommandItem
                    v-for="locale in availableLocales"
                    @select="onAddTranslation(locale.code)"
                    :value="locale.name"
                  >
                    <Flag :locale="locale.code" /> {{ locale.name }}
                  </CommandItem>
                </CommandGroup>
              </CommandList>
            </Command>
          </DropdownMenuContent>
        </DropdownMenu>
      </div>

    </TooltipProvider>
  </div>
</template>

<script setup lang="ts">
import { Button } from '@/Components/Button'
import { Flag } from '@/Components/CountryFlags'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/Components/DropdownMenu'
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/Components/Command'
import { Label } from '@/Components/Label'
import { Switch } from '@/Components/Switch'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/Tooltip'
import { useTranslatableInput, type TranslatableInputProps, type TranslatableInputEmits } from '@/Components/Translations'
import { XIcon } from 'lucide-vue-next'
import { ref } from 'vue'

const emits = defineEmits<TranslatableInputEmits>()
const props = defineProps<TranslatableInputProps>()

const open = ref(false)

const {
  state,
  availableLocales,
  addLocale,
  removeLocale,
  isRequired,
  setPrimaryValue,
  getLocaleName,
} = useTranslatableInput(props, emits)

const onAddTranslation = (locale: string) => {
  open.value = false

  addLocale(locale)
}
</script>
