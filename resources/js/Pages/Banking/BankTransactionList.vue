<template>
  <Head title="Bankové transakcie"/>

  <AppLayout class="pb-12">
    <Empty v-if="! hasConnectedBankAccounts" class="md:justify-start md:mt-24">
      <EmptyHeader>
        <EmptyMedia variant="icon">
          <LandmarkIcon />
        </EmptyMedia>
        <EmptyTitle>Bankové transakcie</EmptyTitle>
        <EmptyDescription>
          Po pripojení bankového účtu automaticky naimportujeme všetky transakcie a spárujeme úhrady s dokladmi.
        </EmptyDescription>
      </EmptyHeader>
      <EmptyContent>
        <div class="flex gap-2">
          <LinkButton :href="route('settings.bank-transactions')">
            <LinkIcon /> Pripojiť účet
          </LinkButton>
        </div>
      </EmptyContent>
      <Button variant="link" plain as-child class="text-muted-foreground" size="sm">
        <a href="https://docs.easydoklad.sk/wip">Zistiť viac <ArrowUpRightIcon /></a>
      </Button>
    </Empty>

    <template v-else>
      <div class="flex flex-row items-end justify-between pt-6 px-4">
        <Heading title="Bankové transakcie" class="mb-0" />

        <div v-if="! transactions.isEmpty" class="inline-flex flex-row gap-2">
          <Button @click="importDialog.activate" size="sm" label="Import" :icon="CloudUploadIcon" variant="outline" />
        </div>
      </div>

      <div class="px-2">
        <DataTable
          :table="transactions"
          empty-table-message="Žiadne bankové transakcie."
          empty-table-description="Zatiaľ neevidujeme žiadne bankové transakcie na pripojených účtoch. Môže to chvíľu trvať, kým sa transakcie objavia."
          :empty-icon="LandmarkIcon"
          @show="showDetail"
        >
          <template #empty-table>
            <Button @click="importDialog.activate" size="sm" label="Import" :icon="CloudUploadIcon" variant="outline" />
          </template>
        </DataTable>
      </div>
    </template>

    <ImportDialog :control="importDialog" />

    <Sheet :control="detailDialog">
      <SheetContent>
        <SheetHeader>
          <SheetTitle>Detail transakcie</SheetTitle>
          <SheetDescription class="font-mono" v-if="transaction">{{ transaction.id }}</SheetDescription>
        </SheetHeader>
        <div class="px-4 flex flex-col gap-4">
          <div v-for="attribute in transactionAttributes" class="flex flex-col gap-0.5">
            <p class="text-sm font-medium">{{ attribute.label }}</p>
            <p class="text-sm text-muted-foreground tabular-nums">{{ attribute.value || '&mdash;' }}</p>
          </div>
        </div>
        <SheetFooter>
          <Button variant="outline" @click="detailDialog.deactivate">Zatvoriť</Button>
        </SheetFooter>
      </SheetContent>
    </Sheet>
  </AppLayout>
</template>

<script setup lang="ts">
import { Button, LinkButton } from '@/Components/Button'
import { DataTable, type DataTableValue } from '@/Components/DataTable'
import Heading from '@/Components/Heading.vue'
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetFooter,
  SheetHeader,
  SheetTitle
} from '@/Components/Sheet'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { useToggle } from '@stacktrace/ui'
import { LandmarkIcon, ArrowUpRightIcon, LinkIcon, CloudUploadIcon } from 'lucide-vue-next'
import { Empty, EmptyContent, EmptyHeader, EmptyMedia, EmptyTitle, EmptyDescription } from '@/Components/Empty'
import { computed, ref } from 'vue'
import ImportDialog from './Dialogs/ImportDialog.vue'

interface Transaction {
  id: string
  date: string
  sentFromIban: string | null
  sentFromName: string | null
  receivedToIban: string | null
  reference: string | null
  description: string | null
  variableSymbol: string | null
  constantSymbol: string | null
  specificSymbol: string | null
  amount: string
}

const importDialog = useToggle()

const props = defineProps<{
  hasConnectedBankAccounts: boolean
  transactions: DataTableValue<Transaction, number>
}>()

const detailDialog = useToggle()
const transaction = ref<Transaction>()

const showDetail = (selection: Array<number>) => {
  const value = props.transactions.rows.find(it => selection.includes(it.key))?.resource

  if (value) {
    transaction.value = value
    detailDialog.activate()
  }
}

const transactionAttributes = computed(() => {
  const attributes: Array<{ label: string, value: string | null }> = []

  const value = transaction.value

  if (value) {
    attributes.push({ label: 'Dátum transakcie', value: value.date })
    attributes.push({ label: 'Názov obchodníka', value: value.sentFromName })
    attributes.push({ label: 'Účet obchodníka', value: value.sentFromIban })
    attributes.push({ label: 'Účet príjemcu', value: value.receivedToIban })
    attributes.push({ label: 'Suma', value: value.amount })
    attributes.push({ label: 'Variabilný symbol', value: value.variableSymbol })
    attributes.push({ label: 'Konštantný symbol', value: value.constantSymbol })
    attributes.push({ label: 'Špecifický symbol', value: value.specificSymbol })
    attributes.push({ label: 'Popis platby', value: value.description })
    attributes.push({ label: 'Referencia', value: value.reference })
  }

  return attributes
})
</script>
