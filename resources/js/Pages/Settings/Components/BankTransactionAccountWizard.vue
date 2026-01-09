<template>
  <!-- Step 1: Select Bank Account Type -->
  <div v-if="step === 'chooseBankAccount'">
    <RadioGroup v-model="form.bank_account_type">
      <div v-for="bankAccountType in BankAccountTypes" @click="form.bank_account_type = bankAccountType.id" class="flex flex-row items-center gap-4 cursor-pointer">
        <component class="w-12 shrink-0" :is="bankAccountType.logo" />

        <div class="flex flex-col">
          <p class="text-sm font-medium">{{ bankAccountType.name }}</p>
          <p class="text-xs text-muted-foreground">{{ bankAccountType.description }}</p>
        </div>

        <RadioGroupItem :value="bankAccountType.id" />
      </div>
    </RadioGroup>

    <div class="mt-6">
      <Button @click="step = 'configureBankAccount'" :disabled="!form.bank_account_type" size="sm" class="w-full">Ďalej</Button>
    </div>
  </div>

  <!-- Step 2: Configure Bank Account -->
  <div v-if="step === 'configureBankAccount'" class="flex flex-col">
    <div class="flex flex-col gap-6">
      <FormControl v-if="selectedBankAccount" label="Typ účtu" required :error="form.errors.bank_account_type">
        <div class="flex flex-row items-center gap-4">
          <component class="w-12 shrink-0" :is="selectedBankAccount.logo" />

          <div class="flex flex-col">
            <p class="text-sm font-medium">{{ selectedBankAccount.name }}</p>
          </div>
        </div>
      </FormControl>

      <FormControl label="Názov účtu" required :error="form.errors.bank_account_name">
        <Input v-model="form.bank_account_name" />
      </FormControl>

      <FormControl label="IBAN" required :error="form.errors.bank_account_iban">
        <Input v-model="form.bank_account_iban" />
      </FormControl>

      <Button @click="save" size="sm" :processing="form.processing">Uložiť</Button>

      <Button @click="reset" variant="link" size="sm" class="text-xs">Vybrať iný typ účtu</Button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Button } from '@/Components/Button'
import { FormControl } from '@/Components/Form'
import { Input } from '@/Components/Input'
import { RadioGroup, RadioGroupItem } from '@/Components/RadioGroup'
import { useForm } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import { BankAccountTypes } from '../'

const emit = defineEmits(['close'])

const step = ref<'chooseBankAccount' | 'configureBankAccount'>('chooseBankAccount')

const form = useForm(() => ({
  bank_account_type: undefined as string | undefined,
  bank_account_name: '',
  bank_account_iban: '',
}))

const reset = () => {
  form.reset()
  step.value = 'chooseBankAccount'
}

const selectedBankAccount = computed(() => BankAccountTypes.find(it => it.id === form.bank_account_type))

const save = () => {
  form.post(route('bank-transaction-accounts.store'), {
    onSuccess: () => {
      emit('close')
    }
  })
}
</script>
