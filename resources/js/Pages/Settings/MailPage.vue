<template>
  <AppLayout>
    <Head title="E-mailová komunikácia"/>

    <SettingsLayout>
      <section>
        <form @submit.prevent="saveVisual" class="space-y-6">
          <div>
            <HeadingSmall title="Vzhľad e-mailov" description="Prispôsobte si vizuálny štýl, farebnosť a obsah pätičky tak, aby odosielané e-maily ladili s vašou identitou."/>

            <div class="inline-flex flex-row mt-2">
              <Button type="button" @click="markdownHelp.activate" variant="link" class="text-muted-foreground px-0 text-xs" plain>Ako formátovať text?</Button>
              <DotIcon class="size-4 mt-[10px]" />
              <Button type="button" @click="replacementsHelp.activate" variant="link" class="text-muted-foreground px-0 text-xs" plain>Dynamické premenné</Button>
            </div>
          </div>

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

          <FormControl label="Hlavička" :error="visualForm.errors.header" help-variant="tooltip" help="Obsah hlavičky pre odosielané správy. Text môžete formátovať pomocou syntaxe Markdown. Dostupné sú aj dynamické zástupné symboly (placeholdery) pre automatické vloženie vašich firemných údajov.">
            <TranslatableTextarea v-model="visualForm.header" rows="3" />
          </FormControl>

          <FormControl label="Logo v hlavičke" :error="visualForm.errors.show_header_logo" help-variant="tooltip" help="Logo v hlavičke sa zobrazí ak je nahrané v sekcii Vizuálna identita.">
            <SwitchControl v-model="visualForm.show_header_logo" class="mt-2">Zobraziť v hlavičke logo firmy</SwitchControl>
          </FormControl>

          <FormControl label="Pätička" :error="visualForm.errors.footer" help-variant="tooltip" help="Obsah pätičky pre odosielané správy. Text môžete formátovať pomocou syntaxe Markdown. Dostupné sú aj dynamické zástupné symboly (placeholdery) pre automatické vloženie vašich firemných údajov.">
            <TranslatableTextarea v-model="visualForm.footer" rows="5" />
          </FormControl>

          <Button type="submit" :processing="visualForm.processing" :recently-successful="visualForm.recentlySuccessful">Uložiť</Button>
        </form>
      </section>

      <section>
        <form @submit.prevent="saveContent">
          <HeadingSmall title="Obsah e-mailov" description="Prispôsobte si obsah automaticky odosielaných e-mailových správ. Na formátovanie obsahu môžete použiť syntax Markdown."/>

          <div class="inline-flex flex-row mb-2">
            <Button type="button" @click="markdownHelp.activate" variant="link" class="text-muted-foreground px-0 text-xs" plain>Ako formátovať text?</Button>
            <DotIcon class="size-4 mt-[10px]" />
            <Button type="button" @click="replacementsHelp.activate" variant="link" class="text-muted-foreground px-0 text-xs" plain>Dynamické premenné</Button>
          </div>

          <div class="border p-4 rounded-md shadow-xs">
            <h4 class="text-sm font-semibold mb-2">Odoslaná faktúra</h4>
            <p class="text-sm text-muted-foreground">Tento email je odoslaný zákaznikovi ak mu z aplikácie odošlete faktúru. E-mail obsahuje aj PDF odoslanej faktúry.</p>

            <FormControl class="mt-4" label="Predmet" :error="contentForm.errors.invoice_sent_subject">
              <TranslatableInput v-model="contentForm.invoice_sent_subject" />
            </FormControl>

            <FormControl class="mt-4" label="Správa" :error="contentForm.errors.invoice_sent_message">
              <TranslatableTextarea
                v-model="contentForm.invoice_sent_message"
                class="w-full"
                rows="10"
              />
            </FormControl>
          </div>

          <Button class="mt-6" type="submit" :processing="contentForm.processing" :recently-successful="contentForm.recentlySuccessful">Uložiť</Button>
        </form>
      </section>

      <section>
        <form @submit.prevent="saveConfig" class="space-y-6">
          <HeadingSmall title="Konfigurácia odosielania" description="Prepojte svoj e-mailový server cez protokol SMTP a nastavte technické parametre pre spoľahlivé doručovanie správ."/>

          <FormControl label="Názov odosielateľa" :error="configForm.errors.sender_name" help-variant="tooltip" help="Pokiaľ názov ponecháte prázdny, ako názov odosielateľa sa použije názov vašej firmy.">
            <Input v-model="configForm.sender_name" class="max-w-xs" placeholder="názov firmy" />
          </FormControl>

          <FormControl label="Adresa odosielateľa">
            <RadioGroup v-model="configForm.sender" class="border border-input rounded-md shadow-xs divide-y divide-input gap-0">
              <div class="flex flex-row p-4">
                <RadioGroupItem id="sender-system" value="system" class="shrink-0 mr-3" />
                <div class="flex flex-col">
                  <Label for="sender-system">doklady@easydoklad.sk</Label>
                  <p class="text-sm mt-1 text-muted-foreground">Všetky e-maily budú odoslané z našej emailovej adresy. Stále však môžete nastaviť, kam chcete nasmerovať odpovede na odoslané emaily.</p>
                </div>
              </div>
              <div class="flex flex-row p-4">
                <RadioGroupItem id="sender-custom" value="custom" class="shrink-0 mr-3" />
                <div class="flex flex-col">
                  <Label for="sender-custom">Vlastná emailová adresa</Label>
                  <p class="text-sm mt-1 text-muted-foreground">Pripojte si vlastný emailový server a odosielajte emaily pod vlastnou emailovou adresou.</p>

                  <div v-if="configForm.sender === 'custom'" class="flex flex-col space-y-4 mt-2">
                    <FormControl label="Spôsob odosielania" :error="configForm.errors.mailer">
                      <FormSelect :options="mailers" v-model="configForm.mailer" class="max-w-xs" placeholder="Vyberte…" />
                    </FormControl>

                    <template v-if="configForm.mailer === 'sendgrid'">
                      <FormControl label="API kľúč" :error="configForm.errors.sendgrid_api_key">
                        <Input v-model="configForm.sendgrid_api_key" />
                      </FormControl>
                    </template>

                    <template v-if="configForm.mailer === 'smtp'">
                      <FormControl label="Host" :error="configForm.errors.smtp_host">
                        <Input v-model="configForm.smtp_host" class="max-w-xs" />
                      </FormControl>

                      <FormControl label="Port" :error="configForm.errors.smtp_port">
                        <Input v-model="configForm.smtp_port" class="max-w-xs" />
                      </FormControl>

                      <FormControl label="Používateľske meno" :error="configForm.errors.smtp_username">
                        <Input v-model="configForm.smtp_username" class="max-w-xs" />
                      </FormControl>

                      <FormControl label="Používateľske heslo" :error="configForm.errors.smtp_password">
                        <Input v-model="configForm.smtp_password" class="max-w-xs" />
                      </FormControl>
                    </template>

                    <FormControl label="E-mailová adresa" :error="configForm.errors.sender_email">
                      <Input v-model="configForm.sender_email" class="max-w-xs" />
                    </FormControl>
                  </div>
                </div>
              </div>
            </RadioGroup>
          </FormControl>

          <FormControl label="Kópia - CC" :error="configErrorFor('carbon_copy')" help-variant="tooltip" help="Na tieto emailové adresy bude odoslaná kópia (CC) každého emailu.">
            <FormTagsInput v-model="configForm.carbon_copy" />
          </FormControl>

          <FormControl label="Skrytá kópia - BCC" :error="configErrorFor('blind_carbon_copy')" help-variant="tooltip" help="Na tieto emailové adresy bude odoslaná skrytá kópia (BCC) každého emailu.">
            <FormTagsInput v-model="configForm.blind_carbon_copy" />
          </FormControl>

          <FormControl label="Odpovedať na" :error="configErrorFor('reply_to')" help-variant="tooltip" help="Na tieto emailové adresy budú smerované odpovede na automatické emaily.">
            <FormTagsInput v-model="configForm.reply_to" />
          </FormControl>

          <Button type="submit" :processing="configForm.processing" :recently-successful="configForm.recentlySuccessful">Uložiť</Button>
        </form>
      </section>
    </SettingsLayout>

    <MarkdownHelpDialog :control="markdownHelp" />
    <ReplacementsHelpDialog :control="replacementsHelp" :replacements="replacements" />
  </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/Components/Button'
import { FormCombobox, FormControl, FormSelect, FormTagsInput } from '@/Components/Form'
import HeadingSmall from '@/Components/HeadingSmall.vue'
import { Input } from '@/Components/Input'
import TranslatableInput from '@/Components/Input/TranslatableInput.vue'
import { Label } from '@/Components/Label'
import { RadioGroup, RadioGroupItem } from '@/Components/RadioGroup'
import { SwitchControl } from '@/Components/Switch'
import { TranslatableTextarea } from '@/Components/Textarea'
import { ToggleGroup, ToggleGroupItem } from '@/Components/ToggleGroup'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/Components/Tooltip'
import { AppLayout, SettingsLayout } from '@/Layouts'
import { Head, useForm } from '@inertiajs/vue3'
import { type SelectOption, useToggle } from '@stacktrace/ui'
import { TextAlignStartIcon, TextAlignCenterIcon, DotIcon } from 'lucide-vue-next'
import { MarkdownHelpDialog, ReplacementsHelpDialog } from '@/Components/Help'
import { type TranslatableString } from '@/Components/Translations'
import { toRaw, watch } from 'vue'

const props = defineProps<{
  font: string
  footer: TranslatableString | null
  header: TranslatableString | null
  showHeaderLogo: boolean
  alignment: string
  sender: 'system' | 'custom'
  senderName: string | null
  senderEmail: string | null
  mailer: string | null
  smtpHost: string | null
  smtpPort: string | null
  smtpUsername: string | null
  smtpPassword: string | null
  sendgridApiKey: string | null
  carbonCopy: Array<string>
  blindCarbonCopy: Array<string>
  replyTo: Array<string>
  invoiceSentSubject: TranslatableString | null
  invoiceSentMessage: TranslatableString | null

  fonts: Array<SelectOption>
  replacements: Array<{
    name: string
    replacements: Record<string, string>
  }>
}>()

const mailers: Array<SelectOption> = [
  { label: 'SMTP', value: 'smtp' },
  { label: 'Sendgrid', value: 'sendgrid' },
]

const visualForm = useForm(() => ({
  font: props.font,
  header: props.header,
  show_header_logo: props.showHeaderLogo,
  footer: props.footer,
  alignment: props.alignment,
}))
const saveVisual = () => {
  visualForm.patch(route('settings.mail.update'), {
    preserveScroll: true,
  })
}

const configForm = useForm(() => ({
  sender: props.sender,
  mailer: props.mailer || undefined,
  smtp_host: props.smtpHost || undefined,
  smtp_port: props.smtpPort || undefined,
  smtp_username: props.smtpUsername || undefined,
  smtp_password: props.smtpPassword || undefined,
  sendgrid_api_key: props.sendgridApiKey || undefined,
  sender_name: props.senderName || undefined,
  sender_email: props.senderEmail || undefined,
  carbon_copy: props.carbonCopy || [],
  blind_carbon_copy: props.blindCarbonCopy || [],
  reply_to: props.replyTo || [],
}))
const saveConfig = () => {
  configForm.patch(route('settings.mail.update'), {
    preserveScroll: true,
  })
}
watch(() => configForm.sender, sender => {
  if (sender === 'system') {
    configForm.mailer = undefined
    configForm.sender_email = undefined
  }
})
watch(() => configForm.mailer, mailer => {
  if (mailer !== 'smtp') {
    configForm.smtp_host = undefined
    configForm.smtp_port = undefined
    configForm.smtp_username = undefined
    configForm.smtp_password = undefined
  }

  if (mailer !== 'sendgrid') {
    configForm.sendgrid_api_key = undefined
  }
})

const configErrorFor = (key: string): string | undefined => {
  const errors = toRaw(configForm.errors) as Record<string, string>
  const errorKeys = Object.keys(errors).filter(k => k == key || k.startsWith(`${key}.`))

  if (errorKeys.length > 0) {
    return errors[errorKeys[0]]
  }

  return undefined
}

const contentForm = useForm(() => ({
  invoice_sent_subject: props.invoiceSentSubject,
  invoice_sent_message: props.invoiceSentMessage,
}))
const saveContent = () => {
  contentForm.patch(route('settings.mail.update'), {
    preserveScroll: true,
  })
}

const markdownHelp = useToggle()
const replacementsHelp = useToggle()
</script>
