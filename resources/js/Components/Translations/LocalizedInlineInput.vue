<template>
  <div>
    <div v-if="state.value.localized" :class="{ 'h-9': state.value.localizedValue.length == 1 }" class="overflow-hidden border divide-y divide-input rounded-md border-input shadow-sm focus-within:ring-1 focus-within:ring-ring">
      <div v-for="entry in state.value.localizedValue" class="flex flex-row relative">
        <div class="w-9 bg-accent text-accent-foreground border-r border-input shrink-0 text-sm font-medium uppercase flex items-center justify-center">
          {{ entry.locale }}
        </div>
        <Input :disabled="disabled" class="border-0 shadow-none rounded-none focus-visible:ring-0" v-model="entry.value" />

        <Button v-if="disabled !== true && state.value.localizedValue.length > 1 && !isRequired(entry.locale)" @click.prevent.stop="removeLocale(entry.locale)" size="sm" class="absolute right-[5px] top-[5px] p-1 h-auto text-muted-foreground" variant="ghost">
          <XIcon class="w-4 h-4" />
        </Button>
      </div>
    </div>

    <Input v-else class="w-full" v-model="state.value.rawValue" />

    <div v-if="disabled !== true" class="flex flex-row justify-between items-center mt-1 h-8">
      <div class="inline-flex flex-row items-center gap-2">
        <Label class="text-xs">Preložiť</Label>
        <Switch size="sm" v-model:checked="state.value.localized" @update:checked="setPrimaryValue" />
      </div>

      <DropdownMenu v-if="state.value.localized && availableLocales.length > 0" v-model:open="open">
        <DropdownMenuTrigger class="text-xs text-foreground font-medium">
          Pridať preklad
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
                >{{ locale.name }}</CommandItem>
              </CommandGroup>
            </CommandList>
          </Command>
        </DropdownMenuContent>
      </DropdownMenu>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Button } from '@/Components/Button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/Components/DropdownMenu'
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/Components/Command'
import { Label } from '@/Components/Label'
import { Switch } from '@/Components/Switch'
import { Input } from '@/Components/Input'
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
} = useTranslatableInput(props, emits)

const onAddTranslation = (locale: string) => {
  open.value = false

  addLocale(locale)
}
</script>
