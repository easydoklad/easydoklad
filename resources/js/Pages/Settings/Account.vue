<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Firma"/>

    <SettingsLayout>
      <section class="space-y-6">
        <HeadingSmall title="Základné informácie" description="Nastavte si základné údaje o Vašej firme"/>

        <form @submit.prevent="saveGeneral" class="space-y-6">
          <FormControl label="Obchodné meno" :error="general.errors.business_name">
            <Input v-model="general.business_name" />
          </FormControl>

          <div class="grid grid-cols-3 gap-4">
            <FormControl label="IČO" :error="general.errors.business_id">
              <Input v-model="general.business_id" />
            </FormControl>

            <FormControl label="DIČ" :error="general.errors.vat_id">
              <Input v-model="general.vat_id" />
            </FormControl>

            <FormControl label="IČDPH" :error="general.errors.eu_vat_id">
              <Input v-model="general.eu_vat_id" />
            </FormControl>
          </div>

          <FormControl :error="general.errors.vat_enabled">
            <CheckboxControl v-model="general.vat_enabled">Platca DPH</CheckboxControl>
          </FormControl>

          <FormControl label="Adresa" :error="general.errors.address_line_one || general.errors.address_line_two || general.errors.address_line_three">
            <div class="flex flex-col gap-2">
              <Input v-model="general.address_line_one" />
              <Input v-model="general.address_line_two" />
              <!--<Input v-model="general.address_line_three" />-->
            </div>
          </FormControl>

          <div class="grid grid-cols-3 gap-4">
            <FormControl label="PSČ" :error="general.errors.address_postal_code">
              <Input v-model="general.address_postal_code" />
            </FormControl>

            <FormControl label="Mesto" :error="general.errors.address_city" class="col-span-2">
              <Input v-model="general.address_city" />
            </FormControl>
          </div>

          <FormControl label="Krajina" :error="general.errors.address_country">
            <FormSelect v-model="general.address_country" :options="countries" />
          </FormControl>

          <FormControl label="Doplňujúce informácie" :error="general.errors.additional_info">
            <Textarea v-model="general.additional_info" rows="4" />
          </FormControl>

          <FormControl label="Webová stránka" :error="general.errors.website" class="max-w-xs">
            <Input v-model="general.website" />
          </FormControl>

          <FormControl label="E-Mail" :error="general.errors.email" class="max-w-xs">
            <Input v-model="general.email" />
          </FormControl>

          <FormControl label="Telefón" :error="general.errors.phone_number" class="max-w-xs">
            <Input v-model="general.phone_number" />
          </FormControl>

          <Button :processing="general.processing" :recently-successful="general.recentlySuccessful">Uložiť</Button>
        </form>
      </section>

      <section class="space-y-6">
        <HeadingSmall title="Bankové spojenie" description="Nastavte si informácie o bankovom účte Vašej firmy"/>

        <form @submit.prevent="saveBank" class="space-y-6">
          <FormControl label="Názov banky" :error="bank.errors.bank_name">
            <Input v-model="bank.bank_name" />
          </FormControl>

          <FormControl label="Adresa banky" :error="bank.errors.bank_address">
            <Input v-model="bank.bank_address" />
          </FormControl>

          <FormControl label="BIC" :error="bank.errors.bank_bic" class="max-w-40">
            <Input v-model="bank.bank_bic" />
          </FormControl>

          <FormControl label="Číslo účtu" :error="bank.errors.bank_account_number" class="max-w-sm">
            <Input v-model="bank.bank_account_number" />
          </FormControl>

          <FormControl label="IBAN" :error="bank.errors.bank_account_iban" class="max-w-sm">
            <Input v-model="bank.bank_account_iban" />
          </FormControl>

          <Button :processing="bank.processing" :recently-successful="bank.recentlySuccessful">Uložiť</Button>
        </form>
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
import { FormControl, FormSelect } from '@/Components/Form'
import { Input } from '@/Components/Input'
import { Textarea } from '@/Components/Textarea'
import { type SelectOption } from '@stacktrace/ui'
import { Button } from '@/Components/Button'
import { CheckboxControl } from '@/Components/Checkbox'

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Firma',
    href: '/settings/appearance',
  },
]

const props = defineProps<{
  id: number
  countries: Array<SelectOption>
  businessName: string | null
  businessId: string | null
  vatId: string | null
  euVatId: string | null
  addressLineOne: string | null
  addressLineTwo: string | null
  addressLineThree: string | null
  addressCity: string | null
  addressPostalCode: string | null
  addressCountry: string | null
  website: string | null
  email: string | null
  additionalInfo: string | null
  phoneNumber: string | null
  bankName: string | null
  bankBic: string | null
  bankAddress: string | null
  bankAccountIban: string | null
  bankAccountNumber: string | null
  vatEnabled: boolean
}>()

const general = useForm(() => ({
  business_name: props.businessName || '',
  business_id: props.businessId || '',
  vat_id: props.vatId || '',
  eu_vat_id: props.euVatId || '',
  address_line_one: props.addressLineOne || '',
  address_line_two: props.addressLineTwo || '',
  address_line_three: props.addressLineThree || '',
  address_city: props.addressCity || '',
  address_postal_code: props.addressPostalCode || '',
  address_country: props.addressCountry || '',
  website: props.website || '',
  email: props.email || '',
  phone_number: props.phoneNumber || '',
  additional_info: props.additionalInfo || '',
  vat_enabled: props.vatEnabled,
}))
const saveGeneral = () => {
  general.patch(route('accounts.update'), { preserveScroll: true })
}

const bank = useForm(() => ({
  bank_name: props.bankName || '',
  bank_address: props.bankAddress || '',
  bank_bic: props.bankBic || '',
  bank_account_number: props.bankAccountNumber || '',
  bank_account_iban: props.bankAccountIban || '',
}))
const saveBank = () => {
  bank.patch(route('bank-accounts.update'), { preserveScroll: true })
}
</script>
