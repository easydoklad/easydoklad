<template>
  <Sheet :control="control">
    <SheetContent>
      <SheetHeader>
        <SheetTitle>Pripojiť bankový účet</SheetTitle>
        <SheetDescription>Vyberte jednu z podporovaných bánk a postupujte podľa uvedených inštrukcií pre dokončenie prepojenia.</SheetDescription>
      </SheetHeader>
      <div class="px-4">
        <BankTransactionAccountWizard @close="control.deactivate" />
      </div>
    </SheetContent>
  </Sheet>
</template>

<script setup lang="ts">
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle
} from '@/Components/Sheet'
import type { Toggle } from '@stacktrace/ui'
import BankTransactionAccountWizard from '../Components/BankTransactionAccountWizard.vue'

defineProps<{
  control: Toggle
}>()
</script>
