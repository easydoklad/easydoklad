<template>
  <div class="flex flex-col gap-6">
    <p class="font-bold text-lg">Odberateľ</p>

    <FormControl label="Obchodné meno" :error="form.errors.customer_business_name" hide-error>
      <FormInlineError>
        <Input autocomplete="off" v-model="form.customer_business_name" @update:model-value="form.clearErrors('customer_business_name')" :disabled="locked" class="disabled:opacity-100" />
      </FormInlineError>
    </FormControl>

    <FormControl label="Adresa" :error="form.errors.customer_address_line_one || form.errors.customer_address_line_two || form.errors.customer_address_line_three" hide-error>
      <div class="flex flex-col gap-2">
        <FormInlineError :error="form.errors.customer_address_line_one || null">
          <Input autocomplete="off" v-model="form.customer_address_line_one" @update:model-value="form.clearErrors('customer_address_line_one')" :disabled="locked" class="disabled:opacity-100" />
        </FormInlineError>
        <FormInlineError :error="form.errors.customer_address_line_two || null">
          <Input autocomplete="off" v-model="form.customer_address_line_two" @update:model-value="form.clearErrors('customer_address_line_two')" :disabled="locked" class="disabled:opacity-100" />
        </FormInlineError>
        <FormInlineError :error="form.errors.customer_address_line_three || null">
          <Input autocomplete="off" v-model="form.customer_address_line_three" @update:model-value="form.clearErrors('customer_address_line_three')" :disabled="locked" class="disabled:opacity-100" />
        </FormInlineError>
      </div>
    </FormControl>

    <div class="grid grid-cols-3 gap-4">
      <FormControl label="PSČ" :error="form.errors.customer_address_postal_code" hide-error>
        <FormInlineError>
          <Input autocomplete="off" v-model="form.customer_address_postal_code" @update:model-value="form.clearErrors('customer_address_postal_code')" :disabled="locked" class="disabled:opacity-100" />
        </FormInlineError>
      </FormControl>

      <FormControl label="Mesto" :error="form.errors.customer_address_city" class="col-span-2" hide-error>
        <FormInlineError>
          <Input autocomplete="off" v-model="form.customer_address_city" @update:model-value="form.clearErrors('customer_address_city')" :disabled="locked" class="disabled:opacity-100" />
        </FormInlineError>
      </FormControl>
    </div>

    <FormControl label="Krajina" :error="form.errors.customer_address_country" hide-error>
      <FormInlineError>
        <FormSelect :options="countries" v-model="form.customer_address_country" @update:model-value="form.clearErrors('customer_address_country')" :disabled="locked" class="disabled:opacity-100" />
      </FormInlineError>
    </FormControl>

    <div class="grid grid-cols-3 gap-4">
      <FormControl label="IČO" :error="form.errors.customer_business_id" hide-error>
        <FormInlineError>
          <Input autocomplete="off" v-model="form.customer_business_id" @update:model-value="form.clearErrors('customer_business_id')" :disabled="locked" class="disabled:opacity-100" />
        </FormInlineError>
      </FormControl>

      <FormControl label="DIČ" :error="form.errors.customer_vat_id" hide-error>
        <FormInlineError>
          <Input autocomplete="off" v-model="form.customer_vat_id" @update:model-value="form.clearErrors('customer_vat_id')" :disabled="locked" class="disabled:opacity-100" />
        </FormInlineError>
      </FormControl>

      <FormControl label="IČDPH" :error="form.errors.customer_eu_vat_id" hide-error>
        <FormInlineError>
          <Input autocomplete="off" v-model="form.customer_eu_vat_id" @update:model-value="form.clearErrors('customer_eu_vat_id')" :disabled="locked" class="disabled:opacity-100" />
        </FormInlineError>
      </FormControl>
    </div>

    <FormControl
      label="Doplňujúce informácie"
      :error="form.errors.customer_additional_info"
      :help="!locked ? 'Vyplňte ak potrebujete uviesť ďalšie informácie k odberateľovi.' : undefined"
      hide-error
    >
      <FormInlineError>
        <Textarea autocomplete="off" v-model="form.customer_additional_info" @update:model-value="form.clearErrors('customer_additional_info')" :disabled="locked" class="disabled:opacity-100" />
      </FormInlineError>
    </FormControl>

    <div class="grid grid-cols-2 gap-4">
      <FormControl label="E-Mail" :error="form.errors.customer_email" hide-error>
        <FormInlineError>
          <Input autocomplete="off" v-model="form.customer_email" @update:model-value="form.clearErrors('customer_email')" :disabled="locked" class="disabled:opacity-100" />
        </FormInlineError>
      </FormControl>

      <FormControl label="Tel. číslo" :error="form.errors.customer_phone_number" hide-error>
        <FormInlineError>
          <Input autocomplete="off" v-model="form.customer_phone_number" @update:model-value="form.clearErrors('customer_phone_number')" :disabled="locked" class="disabled:opacity-100" />
        </FormInlineError>
      </FormControl>
    </div>

    <!--<FormControl label="Webová stránka" :error="form.errors.customer_website" hide-error>-->
    <!--  <FormInlineError>-->
    <!--    <Input autocomplete="off" v-model="form.customer_website" @update:model-value="form.clearErrors('customer_website')" :disabled="locked" class="disabled:opacity-100" />-->
    <!--  </FormInlineError>-->
    <!--</FormControl>-->
  </div>
</template>

<script setup lang="ts">
import { FormControl, FormInlineError, FormSelect } from "@/Components/Form";
import { Input } from "@/Components/Input";
import { Textarea } from "@/Components/Textarea";
import { injectInvoiceFormContext } from "@/Pages/Invoices/Form/index.ts";

const { form, locked, countries } = injectInvoiceFormContext()
</script>
