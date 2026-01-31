<template>
  <AppLayout>
    <Head title="E-mailová komunikácia"/>

    <SettingsLayout>
      <section>
        <form @submit.prevent="saveVisual" class="space-y-6">
          <HeadingSmall title="Vzhľad e-mailov" description="Prispôsobte si vizuálny štýl, farebnosť a obsah pätičky tak, aby odosielané e-maily ladili s vašou identitou."/>

          <FormControl label="Zarovnanie prvkov" :error="visualForm.errors.alignment" help-variant="tooltip" help="Nastavte si ako bude zarovnané logo v hlavičke a text v pätičke. Obsah emailu je vždy zobrazený na celú šírku a zarovnaný vľavo.">
            <TooltipProvider :delay-duration="0">
              <ToggleGroup v-model="visualForm.alignment" variant="outline">
                <ToggleGroupItem value="left">
                  <Tooltip>
                    <TooltipTrigger type="button">
                        <TextAlignStartIcon class="size-4" />
                    </TooltipTrigger>
                    <TooltipContent side="bottom">Vľavo</TooltipContent>
                  </Tooltip>
                </ToggleGroupItem>

                <ToggleGroupItem value="center">
                  <Tooltip>
                    <TooltipTrigger type="button">
                      <TextAlignCenterIcon class="size-4" />
                    </TooltipTrigger>
                    <TooltipContent side="bottom">Na stred</TooltipContent>
                  </Tooltip>
                </ToggleGroupItem>
              </ToggleGroup>
            </TooltipProvider>
          </FormControl>

          <FormControl label="Font" :error="visualForm.errors.font">
            <FormCombobox :options="fonts" v-model="visualForm.font" class="max-w-xs" />
          </FormControl>

          <FormControl label="Pätička" :error="visualForm.errors.footer" help-variant="tooltip" help="Obsah pätičky pre odosielané správy. Text môžete formátovať pomocou syntaxe Markdown. Dostupné sú aj dynamické zástupné symboly (placeholdery) pre automatické vloženie vašich firemných údajov.">
            <Textarea v-model="visualForm.footer" rows="4" />
            <div class="inline-flex flex-row">
              <Button @click="markdownHelp.activate" variant="link" class="text-muted-foreground px-0 text-xs -mt-1" plain>Ako formátovať text?</Button>
              <DotIcon class="size-4 mt-[7px]" />
              <Button @click="replacementsHelp.activate" variant="link" class="text-muted-foreground px-0 text-xs -mt-1" plain>Dynamické premenné</Button>
            </div>
          </FormControl>

          <Button type="submit" :processing="visualForm.processing" :recently-successful="visualForm.recentlySuccessful">Uložiť</Button>
        </form>
      </section>

      <section class="space-y-6">
        <HeadingSmall title="Konfigurácia odosielania" description="Prepojte svoj e-mailový server cez protokol SMTP a nastavte technické parametre pre spoľahlivé doručovanie správ."/>

      </section>
    </SettingsLayout>

    <MarkdownHelpDialog :control="markdownHelp" />
    <ReplacementsHelpDialog :control="replacementsHelp" :replacements="replacements" />
  </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/Components/Button'
import { FormCombobox, FormControl } from '@/Components/Form'
import HeadingSmall from '@/Components/HeadingSmall.vue'
import { Textarea } from '@/Components/Textarea'
import { ToggleGroup, ToggleGroupItem } from '@/Components/ToggleGroup'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/Tooltip'
import { AppLayout, SettingsLayout } from '@/Layouts'
import { Head, useForm } from '@inertiajs/vue3'
import { type SelectOption, useToggle } from '@stacktrace/ui'
import { TextAlignStartIcon, TextAlignCenterIcon, DotIcon } from 'lucide-vue-next'
import { MarkdownHelpDialog, ReplacementsHelpDialog } from '@/Components/Help'

const props = defineProps<{
  font: string
  footer: string
  alignment: string

  fonts: Array<SelectOption>
  replacements: Array<{
    name: string
    replacements: Record<string, string>
  }>
}>()

const visualForm = useForm(() => ({
  font: props.font,
  footer: props.footer,
  alignment: props.alignment,
}))
const saveVisual = () => {
  visualForm.patch(route('settings.mail.update'))
}

const markdownHelp = useToggle()
const replacementsHelp = useToggle()
</script>
