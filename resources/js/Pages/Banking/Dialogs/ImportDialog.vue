<template>
  <Dialog :control="control">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>Import transakcií</DialogTitle>
        <DialogDescription>Bankové transakcie môžete aj importovať manuálne nahraním elektronického CAMT.053 výpisu. Import prebieha na pozadí.</DialogDescription>
      </DialogHeader>
      <TemporaryFileInput scope="BankTransactionsCamt" v-model:file="form.file" :image="false" />
      <DialogFooter>
        <Button @click="control.deactivate" variant="outline">Zrušiť</Button>
        <Button :processing="form.processing" @click="save">Importovať</Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<script setup lang="ts">
import { Button } from '@/Components/Button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle
} from '@/Components/Dialog'
import { TemporaryFileInput } from '@/Components/TemporaryFileInput'
import { useForm } from '@inertiajs/vue3'
import type { Toggle } from '@stacktrace/ui'

const props = defineProps<{
  control: Toggle
}>()

const form = useForm(() => ({
  file: null,
}))

const save = () => {
  form.post(route('bank-transactions.camt-import'), {
    onSuccess: () => {
      props.control.deactivate()
    }
  })
}
</script>
