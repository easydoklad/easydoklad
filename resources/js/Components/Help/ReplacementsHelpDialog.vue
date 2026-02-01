<template>
  <Sheet :control="control">
    <SheetContent class="sm:max-w-lg">
      <SheetHeader>
        <SheetTitle>Dynamické premenné</SheetTitle>
        <SheetDescription>Prehľad zástupných symbolov, ktoré systém automaticky nahradí konkrétnymi údajmi o doklade, odberateľovi alebo vašej firme.</SheetDescription>
      </SheetHeader>

      <div class="flex flex-col gap-6">
        <div v-for="group in replacements">
          <p class="text-sm pl-4 font-bold mb-3">{{ group.name }}</p>

          <Table class="border-y">
            <TableBody>
              <TableRow v-for="(description, replacement) in group.replacements" class="cursor-pointer" @click="copyReplacement(replacement)">
                <TableCell class="pl-4"><code class="text-xs">:{{ replacement }}</code></TableCell>
                <TableCell class="pr-4"><span>{{ description }}</span></TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>
      <SheetFooter>
        <Button @click="control.deactivate" variant="outline">Zatvoriť</Button>
      </SheetFooter>
    </SheetContent>
  </Sheet>
</template>

<script setup lang="ts">
import { Button } from '@/Components/Button'
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetFooter,
  SheetHeader,
  SheetTitle
} from '@/Components/Sheet'
import { Table, TableBody, TableCell, TableRow } from '@/Components/Table'
import type { Toggle } from '@stacktrace/ui'
import { useClipboard } from '@vueuse/core'
import { toast } from 'vue-sonner'

defineProps<{
  control: Toggle
  replacements: Array<{
    name: string
    replacements: Record<string, string>
  }>
}>()

const { copy } = useClipboard({ legacy: true })

const copyReplacement = (value: string) => {
  const val = `:${value}`

  toast('Skopírované do schránky!', { description: val })
  copy(val)
}
</script>
