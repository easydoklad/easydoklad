<template>
  <div class="flex flex-col gap-6">
    <p class="font-bold text-lg">Nastavenia dokladu</p>

    <FormControl label="DPH" :error="form.errors.vat_enabled || form.errors.vat_reverse_charge" hide-error>
      <div class="flex flex-col gap-2">
        <CheckboxControl
          v-model="form.vat_enabled"
          :error="form.errors.vat_enabled"
          @update:model-value="form.clearErrors('vat_enabled')"
          :disabled="locked"
        >Zapnúť DPH</CheckboxControl>

        <CheckboxControl
          v-model="form.vat_reverse_charge"
          :error="form.errors.vat_reverse_charge"
          @update:model-value="form.clearErrors('vat_reverse_charge')"
          :disabled="locked"
        >Prenesenie daňovej povinnosti</CheckboxControl>
      </div>
    </FormControl>

    <FormControl label="Vystavil" :error="form.errors.issued_by || form.errors.issued_by_email || form.errors.issued_by_website || form.errors.issued_by_phone_number" hide-error>
      <div class="flex flex-col gap-2">
        <FormInlineError :error="form.errors.issued_by || null">
          <Input
            placeholder="Meno a priezvisko"
            v-model="form.issued_by"
            @update:model-value="form.clearErrors('issued_by')"
            :disabled="locked"
            class="disabled:opacity-100"
            autocomplete="off"
          />
        </FormInlineError>

        <FormInlineError :error="form.errors.issued_by_phone_number || null">
          <Input
            placeholder="Tel. číslo"
            v-model="form.issued_by_phone_number"
            @update:model-value="form.clearErrors('issued_by_phone_number')"
            :disabled="locked"
            class="disabled:opacity-100"
            autocomplete="off"
          />
        </FormInlineError>

        <FormInlineError :error="form.errors.issued_by_email || null">
          <Input
            placeholder="E-Mail"
            v-model="form.issued_by_email"
            @update:model-value="form.clearErrors('issued_by_email')"
            :disabled="locked"
            class="disabled:opacity-100"
            autocomplete="off"
          />
        </FormInlineError>

        <FormInlineError :error="form.errors.issued_by_website || null">
          <Input
            placeholder="Webová stránka"
            v-model="form.issued_by_website"
            @update:model-value="form.clearErrors('issued_by_website')"
            :disabled="locked"
            class="disabled:opacity-100"
            autocomplete="off"
          />
        </FormInlineError>
      </div>
    </FormControl>

    <FormControl label="Šablóna" :error="form.errors.template" hide-error>
      <FormInlineError>
        <FormSelect :options="templates" v-model="form.template" @update:model-value="form.clearErrors('template')" :disabled="locked" class="disabled:opacity-100" />
      </FormInlineError>
    </FormControl>

    <FormControl
      label="Poznámka v pätičke"
      :error="form.errors.footer_note"
      :help="!locked ? 'Tu môžete uviesť poznámku o zápise spoločnosti v obchodnom registri.' : undefined"
      hide-error
    >
      <FormInlineError>
        <Textarea autocomplete="off" v-model="form.footer_note" @update:model-value="form.clearErrors('footer_note')" :disabled="locked" class="disabled:opacity-100" />
      </FormInlineError>
    </FormControl>

    <div class="grid grid-cols-2 gap-4" v-if="! (hideLogo && hideSignature)">
      <FormControl label="Logo" v-if="! hideLogo">
        <TemporaryFileInput
          class="w-full"
          :source="invoice.logoUrl"
          scope="InvoiceLogo"
          v-model:file="form.logo"
          v-model:remove="form.remove_logo"
          :disabled="locked"
          drop-class="h-36"
          :show-icon="false"
        />
      </FormControl>

      <FormControl label="Podpis" v-if="! hideSignature">
        <TemporaryFileInput
          class="w-full"
          :source="invoice.signatureUrl"
          scope="InvoiceSignature"
          v-model:file="form.signature"
          v-model:remove="form.remove_signature"
          :disabled="locked"
          drop-class="h-36"
          :show-icon="false"
        />
      </FormControl>
    </div>
  </div>
</template>

<script setup lang="ts">
import { CheckboxControl } from "@/Components/Checkbox";
import { FormControl, FormInlineError, FormSelect } from "@/Components/Form";
import { Input } from "@/Components/Input";
import { Textarea } from "@/Components/Textarea";
import { injectInvoiceFormContext } from "@/Pages/Invoices/Form"
import { TemporaryFileInput } from "@/Components/TemporaryFileInput"
import { computed } from "vue";

const { form, locked, templates, invoice } = injectInvoiceFormContext()

const hideLogo = computed(() => locked.value && !invoice.value.logoUrl)
const hideSignature = computed(() => locked.value && !invoice.value.signatureUrl)
</script>
