<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Faktúry"/>

    <SettingsLayout>
      <section class="space-y-6">
        <HeadingSmall title="Predvolené nastavenia faktúr" description="Prispôsobte si predvolené možnosti použité pri tvorbe nových faktúr"/>

        <form @submit.prevent="save" class="space-y-6">
          <FormControl label="Formát čísla faktúry" :error="form.errors.numbering_format">
            <Input v-model="form.numbering_format" class="max-w-sm" />
          </FormControl>

          <FormControl label="Formát variabilného symbolu" :error="form.errors.variable_symbol_format">
            <Input v-model="form.variable_symbol_format" class="max-w-sm" />
          </FormControl>

          <FormControl label="Nasledujúce poradové číslo" :error="form.errors.next_number">
            <Input v-model="form.next_number" class="max-w-36" />
          </FormControl>

          <FormControl label="Počet dní splatnosti" :error="form.errors.due_days">
            <Input v-model="form.due_days" class="max-w-36" />
          </FormControl>

          <FormControl v-if="vatEnabled" label="Sadzba DPH" :error="form.errors.default_vat_rate">
            <Input v-model="form.default_vat_rate" class="max-w-36" />
          </FormControl>

          <FormControl label="Spôsob platby" :error="form.errors.payment_method" class="max-w-sm">
            <FormSelect :options="paymentMethods" v-model="form.payment_method" />
          </FormControl>

          <FormControl label="Šablóna" :error="form.errors.template" class="max-w-sm">
            <FormSelect :options="templates" v-model="form.template" />
          </FormControl>

          <FormControl label="Informácia v pätičke" :error="form.errors.footer_note">
            <Textarea v-model="form.footer_note" rows="3" />
          </FormControl>

          <Button :processing="form.processing" :recently-successful="form.recentlySuccessful">Uložiť</Button>
        </form>
      </section>

      <section class="space-y-6">
        <HeadingSmall title="Logo" description="Nastavte si logo zobrazené v hlavičke faktúry. Obrázok musí mať rozmery minimálne 100x100px a maximálne 400x400px. Odporúčame priehľadný PNG obrázok alebo obrázok s bielym pozadím. Maximálna veľkosť súboru nesmie presiahnuť 8 MB."/>

        <div class="space-y-6">
          <TemporaryFileInput
            :error="logoForm.errors.file || logoForm.errors.remove"
            scope="InvoiceLogo"
            :source="logoFileUrl"
            v-model:file="logoForm.file"
            v-model:remove="logoForm.remove"
          />

          <Button @click="saveLogo" :processing="logoForm.processing" :recently-successful="logoForm.recentlySuccessful">Uložiť</Button>
        </div>
      </section>

      <section class="space-y-6">
        <HeadingSmall title="Podpis" description="Nastavte si podpis zbrazený na Vašich faktúrach. Obrázok musí mať rozmery minimálne 100x100px a maximálne 400x400px. Odporúčame priehľadný PNG obrázok alebo obrázok s bielym pozadím. Maximálna veľkosť súboru nesmie presiahnuť 8 MB."/>

        <div class="space-y-6">
          <TemporaryFileInput
            :error="signatureForm.errors.file || signatureForm.errors.remove"
            scope="InvoiceSignature"
            :source="signatureFileUrl"
            v-model:file="signatureForm.file"
            v-model:remove="signatureForm.remove"
          />

          <Button @click="saveSignature" :processing="signatureForm.processing" :recently-successful="signatureForm.recentlySuccessful">Uložiť</Button>
        </div>
      </section>
    </SettingsLayout>
  </AppLayout>
</template>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import HeadingSmall from '@/Components/HeadingSmall.vue'
import { type BreadcrumbItem } from '@/Types'
import AppLayout from '@/Layouts/AppLayout.vue'
import { SettingsLayout } from '@/Layouts'
import { FormControl, FormSelect } from "@/Components/Form";
import { Input } from '@/Components/Input'
import { Textarea } from '@/Components/Textarea'
import { type SelectOption } from '@stacktrace/ui'
import { Button } from '@/Components/Button'
import { TemporaryFileInput } from '@/Components/TemporaryFileInput'

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Invoice',
    href: '/settings/appearance',
  },
]

const props = defineProps<{
  vatEnabled: boolean
  numberingFormat: string
  variableSymbolFormat: string
  defaultVatRate: number
  dueDays: number
  footerNote: string | null
  template: string
  templates: Array<SelectOption>
  paymentMethod: string
  paymentMethods: Array<SelectOption>
  signatureFileUrl: string | null
  logoFileUrl: string | null
  nextNumber: number
}>()

const form = useForm(() => ({
  numbering_format: props.numberingFormat,
  variable_symbol_format: props.variableSymbolFormat,
  default_vat_rate: props.defaultVatRate,
  due_days: props.dueDays,
  footer_note: props.footerNote || '',
  template: props.template,
  payment_method: props.paymentMethod,
  next_number: props.nextNumber,
}))
const save = () => {
  form.patch(route('invoices.settings.update'), { preserveScroll: true })
}

const logoForm = useForm(() => ({
  file: null,
  remove: false,
}))
const saveLogo = () => {
  logoForm.patch(route('invoices.settings.logo'), {
    preserveScroll: true,
    onSuccess: () => logoForm.reset()
  })
}

const signatureForm = useForm(() => ({
  file: null,
  remove: false,
}))
const saveSignature = () => {
  signatureForm.patch(route('invoices.settings.signature'), {
    preserveScroll: true,
    onSuccess: () => signatureForm.reset()
  })
}
</script>
