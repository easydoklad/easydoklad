<template>
  <AppLayout>
    <Head title="Bankové transakcie"/>

    <SettingsLayout>
      <section class="space-y-6">
        <HeadingSmall title="Bankové účty" description="Prepojte bankové účty z podporovaných bánk a majte transakcie aj úhrady dokladov vždy automaticky spárované."/>

        <div v-if="bankAccounts.length === 0" class="flex flex-col border items-center justify-center gap-4 px-6 py-12 border-dashed rounded-md">
          <p class="text-sm">Zatiaľ nemáte pripojené žiadne bankové účty.</p>

          <Button @click="bankTransactionAccountFormDialog.activate" size="sm" variant="outline" :icon="PlusIcon" label="Pripojiť bankový účet" />
        </div>

        <template v-else>
          <div class="flex flex-col divide-y">
            <div v-for="bankAccount in bankAccounts" class="flex flex-row py-2 items-center">
              <div class="mr-4">
                <component class="w-10" v-if="findLogo(bankAccount.bankAccountType.value)" :is="findLogo(bankAccount.bankAccountType.value)" />
              </div>

              <div class="flex flex-col">
                <p class="text-sm font-medium">{{ bankAccount.name }}</p>
                <p class="text-xs text-muted-foreground">{{ bankAccount.iban }}</p>
              </div>

              <div class="inline-flex flex-row items-center ml-auto">
                <Button v-if="bankAccount.mailIntegration" @click="showMailIntegrationHelp(bankAccount.mailIntegration)" variant="ghost" size="icon">
                  <InfoIcon />
                </Button>

                <DropdownMenu>
                  <DropdownMenuTrigger>
                    <Button variant="ghost" size="icon">
                      <EllipsisIcon />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuItem @select="confirmDestroyBankAccount(bankAccount.id)" variant="destructive">Odstrániť</DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </div>
          </div>

          <Button @click="bankTransactionAccountFormDialog.activate" size="sm" variant="outline" :icon="PlusIcon" label="Pripojiť bankový účet" />
        </template>
      </section>
    </SettingsLayout>

    <BankTransactionAccountDialog
      :control="bankTransactionAccountFormDialog"
    />

    <Dialog :control="completeMailIntegrationDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Dokončenie prepojenia bankového účtu</DialogTitle>
          <DialogDescription>Pre dokončenie pripojenia bankového účtu nastavte odosielanie emaiových notifikácií na uvedený email v Internet bankingu.</DialogDescription>
        </DialogHeader>
        <div v-if="mailIntegrationDetails" class="flex flex-col gap-6">
          <FormControl label="E-Mailová adresa" help="Na túto emailovú adresu nasmerujte notifikácie o transakciách.">
            <CopyInput :value="mailIntegrationDetails.email" />
          </FormControl>

          <p class="text-sm" v-if="mailIntegrationDetails.helpLink">
            Presné informácie ako nastaviť emailové notifikácie pre tento účet nájdete v <a class="font-medium underline" :href="mailIntegrationDetails.helpLink" target="_blank">dokumentácií</a>.
          </p>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="completeMailIntegrationDialog.deactivate">Zatvoriť</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <AlertDialog :control="destroyDialog">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Odstrániť bankový účet</AlertDialogTitle>
          <AlertDialogDescription>Naozaj chcete odstrániť tento bankový účet? Všetky bankové transakcie prijaté prostredníctvom tohoto prepojenia budú odstránené.</AlertDialogDescription>
        </AlertDialogHeader>
        <div class="pb-4 pt-2">
          <CheckboxControl v-model="destroyPayments">Odstrániť aj všetky úhrady faktúr, ktoré boli prijaté prostredníctvom tohoto prepojenia.</CheckboxControl>
        </div>
        <AlertDialogFooter>
          <AlertDialogCancel>Zrušiť</AlertDialogCancel>
          <Button :processing="destroyForm.processing" @click="destroyBankAccount">Odstrániť účet</Button>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </AppLayout>
</template>

<script setup lang="ts">
import {
  AlertDialog, AlertDialogCancel,
  AlertDialogContent, AlertDialogDescription, AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle
} from '@/Components/AlertDialog'
import { Button } from '@/Components/Button'
import { CheckboxControl } from '@/Components/Checkbox'
import { CopyInput } from '@/Components/CopyInput'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle
} from '@/Components/Dialog'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger
} from '@/Components/DropdownMenu'
import { FormControl } from '@/Components/Form'
import HeadingSmall from '@/Components/HeadingSmall.vue'
import { useFlash } from '@/Composables/useFlash.ts'
import AppLayout from '@/Layouts/AppLayout.vue'
import SettingsLayout from '@/Layouts/Settings/Layout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { type SelectOption, useToggle } from '@stacktrace/ui'
import { nextTick, ref } from 'vue'
import BankTransactionAccountDialog from './Dialogs/BankTransactionAccountDialog.vue'
import { PlusIcon, EllipsisIcon, InfoIcon } from 'lucide-vue-next'
import { BankAccountTypes } from '.'

interface MailIntegrationDetails {
  email: string
  helpLink: string | null
}

defineProps<{
  bankAccounts: Array<{
    id: string
    name: string
    iban: string
    bankAccountType: SelectOption
    mailIntegration: MailIntegrationDetails | null
  }>
}>()

const bankTransactionAccountFormDialog = useToggle()

const completeMailIntegrationDialog = useToggle()
const mailIntegrationDetails = ref<MailIntegrationDetails | undefined>()

const showMailIntegrationHelp = (details: MailIntegrationDetails) => {
  mailIntegrationDetails.value = details
  nextTick(() => completeMailIntegrationDialog.activate())
}

useFlash('completeBankMailIntegration', event => showMailIntegrationHelp(event))

const findLogo = (type: string) => BankAccountTypes.find(it => it.id === type)?.logo

const destroyDialog = useToggle()
const destroyBankAccountId = ref<string | undefined>()
const destroyPayments = ref(false)
const confirmDestroyBankAccount = (id: string) => {
  destroyBankAccountId.value = id
  destroyPayments.value = false
  destroyDialog.activate()
}
const destroyForm = useForm({})
const destroyBankAccount = () => {
  const id = destroyBankAccountId.value

  if (id) {
    destroyForm.delete(route('bank-transaction-accounts.destroy', { account: id, _query: { payments: destroyPayments.value } }), {
      onSuccess: () => {
        destroyDialog.deactivate()
      }
    })
  }
}
</script>
