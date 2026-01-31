<template>
  <Sheet :control="control">
    <SheetContent class="sm:max-w-lg">
      <SheetHeader>
        <SheetTitle>Ako formátovať text?</SheetTitle>
        <SheetDescription>Stručný prehľad syntaxe Markdown, pomocou ktorej môžete upravovať vzhľad textu (napr. tučné písmo, odkazy alebo zoznamy).</SheetDescription>
      </SheetHeader>
      <Table>
        <TableHeader>
          <TableRow class="hover:bg-transparent">
            <TableHead class="pl-4">Chcem dosiahnuť</TableHead>
            <TableHead class="pr-4">Napíšem</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow class="hover:bg-transparent">
            <TableCell class="pl-4"><span class="font-bold">Tučné písmo</span></TableCell>
            <TableCell class="pr-4"><code>**text**</code></TableCell>
          </TableRow>
          <TableRow class="hover:bg-transparent">
            <TableCell class="pl-4"><span class="italic">Kurzíva</span></TableCell>
            <TableCell class="pr-4"><code>*text*</code></TableCell>
          </TableRow>
          <TableRow class="hover:bg-transparent">
            <TableCell class="pl-4"><span class="underline">Odkaz na web</span></TableCell>
            <TableCell class="pr-4"><code>[názov](https://url.sk)</code></TableCell>
          </TableRow>
          <TableRow class="hover:bg-transparent">
            <TableCell class="pl-4"><span>Bodový zoznam</span></TableCell>
            <TableCell class="pr-4"><code>- položka</code></TableCell>
          </TableRow>
          <TableRow class="hover:bg-transparent">
            <TableCell class="pl-4"><span>Horizontálna čiara</span></TableCell>
            <TableCell class="pr-4"><code>---</code></TableCell>
          </TableRow>
        </TableBody>
      </Table>
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
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/Table'
import type { Toggle } from '@stacktrace/ui'

defineProps<{
  control: Toggle
}>()
</script>
