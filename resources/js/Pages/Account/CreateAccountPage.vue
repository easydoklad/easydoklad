<template>
  <AppLayout>
    <Head title="Nová firma" />

    <div class="flex items-center justify-center p-12">
      <div class="max-w-lg flex flex-col items-center">
        <h3 class="text-lg font-medium tracking-tight text-center">{{ isFirstAccount ? 'Poďme na vašu prvú firmu' : 'Nová firma' }}</h3>
        <p class="text-muted-foreground text-sm/relaxed text-center mt-2">{{ isFirstAccount ? 'Vyplňte základné údaje, aby sme mohli pripraviť vaše prvé faktúry.' : 'Pridajte ďalší subjekt, pre ktorý chcete evidovať faktúry.' }}</p>

        <div class="mt-8 w-full flex flex-col gap-4">
          <FormControl label="Obchodné meno" :error="form.errors.business_name">
            <Input v-model="form.business_name" />
          </FormControl>

          <div class="grid grid-cols-3 gap-4">
            <FormControl label="IČO" :error="form.errors.business_id">
              <Input v-model="form.business_id" />
            </FormControl>

            <FormControl label="DIČ" :error="form.errors.vat_id">
              <Input v-model="form.vat_id" />
            </FormControl>

            <FormControl label="IČDPH" :error="form.errors.eu_vat_id">
              <Input v-model="form.eu_vat_id" />
            </FormControl>
          </div>

          <FormControl :error="form.errors.vat_enabled">
            <CheckboxControl v-model="form.vat_enabled">Firma je platcom DPH.</CheckboxControl>
          </FormControl>

          <FormControl label="Adresa" :error="form.errors.address_line_one || form.errors.address_line_two">
            <Input v-model="form.address_line_one" />
            <Input v-model="form.address_line_two" class="mt-3" />
          </FormControl>

          <div class="grid grid-cols-2 gap-4">
            <FormControl label="PSČ" :error="form.errors.address_postal_code">
              <Input v-model="form.address_postal_code" />
            </FormControl>

            <FormControl label="Mesto" :error="form.errors.address_city">
              <Input v-model="form.address_city" />
            </FormControl>
          </div>

          <FormControl label="Krajina" :error="form.errors.address_country">
            <FormSelect :options="countries" v-model="form.address_country" />
          </FormControl>
        </div>

        <Button @click="create" :processing="form.processing" class="w-full mt-10">{{ isFirstAccount ? 'Začať' : 'Vytvoriť' }} <MoveRightIcon v-if="isFirstAccount" /></Button>

        <LinkButton class="w-full mt-4" v-if="!isFirstAccount" variant="ghost" :href="route('dashboard')">Zrušiť</LinkButton>
      </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import { Button, LinkButton } from '@/Components/Button'
import { CheckboxControl } from '@/Components/Checkbox'
import { FormControl, FormSelect } from '@/Components/Form'
import { Input } from '@/Components/Input'
import { AppLayout } from '@/Layouts'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import type { SelectOption } from '@stacktrace/ui'
import { computed } from 'vue'
import { MoveRightIcon } from 'lucide-vue-next'

const page = usePage()

const user = computed(() => page.props.auth.user)
const isFirstAccount = computed(() => user.value.accounts.length === 0)

const props = defineProps<{
  countries: Array<SelectOption>
  defaultCountry: string
}>()

const form = useForm(() => ({
  business_name: '',
  business_id: '',
  vat_id: '',
  eu_vat_id: '',
  vat_enabled: false,
  address_line_one: '',
  address_line_two: '',
  address_city: '',
  address_postal_code: '',
  address_country: props.defaultCountry,
}))

const create = () => {
  form.post(route('accounts.store'))
}
</script>
