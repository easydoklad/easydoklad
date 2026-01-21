<template>
  <AppLayout>
    <Head title="API kľúče"/>

    <SettingsLayout>
      <section class="space-y-6">
        <HeadingSmall title="API kľúče" description="Tieto kľúče slúžia na autorizáciu pri používaní API."/>

        <Empty v-if="apiKeys.length === 0">
          <EmptyHeader>
            <EmptyMedia variant="icon">
              <KeySquareIcon />
            </EmptyMedia>
            <EmptyTitle>Žiadne API kľúče.</EmptyTitle>
            <EmptyDescription>Zatiaľ nemáte vytvorené žiadne API kľúče.</EmptyDescription>
          </EmptyHeader>
          <EmptyContent>
            <Button @click="createApiKeyDialog.activate" size="sm" variant="outline" :icon="PlusIcon" label="Vytvoriť API kľúč" />
          </EmptyContent>
        </Empty>

        <template v-else>
          <div class="flex flex-col divide-y">
            <div v-for="apiKey in apiKeys" class="flex flex-row py-2 items-center">
              <div class="flex flex-col">
                <p class="text-sm font-medium">{{ apiKey.name }}</p>
                <p class="text-xs text-muted-foreground">{{ apiKey.expiresAt ? `Exspirácia: ${apiKey.expiresAt}` : 'Bez exspirácie' }}</p>
              </div>

              <div class="inline-flex flex-row items-center ml-auto">
                <DropdownMenu>
                  <DropdownMenuTrigger>
                    <Button variant="ghost" size="icon">
                      <EllipsisVerticalIcon />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuItem @select="confirmRevoke(apiKey.id)" variant="destructive"><Trash2Icon /> Odstrániť</DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </div>
          </div>

          <Button @click="createApiKeyDialog.activate" size="sm" variant="outline" :icon="PlusIcon" label="Vytvoriť API kľúč" />
        </template>
      </section>
    </SettingsLayout>

    <Dialog :control="createApiKeyDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Vytvoriť API kľúč</DialogTitle>
          <DialogDescription>Pre zvýšenie bezpčenosti odporúčame API kľúč pravidelne rotovať a nastaviť mu exspiráciu.</DialogDescription>
        </DialogHeader>
        <div class="flex flex-col gap-6 mb-4">
          <FormControl label="Názov" required :error="form.errors.name">
            <Input v-model="form.name" />
          </FormControl>

          <FormControl label="Exspirácia" :error="form.errors.expires_at">
            <DatePicker v-model="form.expires_at" placeholder="bez exspirácie" :min="minExpirationDate" :max="maxExpirationDate" />
          </FormControl>
        </div>
        <DialogFooter>
          <Button @click="createApiKeyDialog.deactivate" variant="outline">Zrušiť</Button>
          <Button @click="create" :processing="form.processing">Vytvoriť</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Dialog :control="showTokenDialog">
      <DialogContent @interact-outside.prevent>
        <DialogHeader>
          <DialogTitle>API kľúč</DialogTitle>
          <DialogDescription>Starostlivo si uschovajte vygenerovaný API kľúč. Tento kľúč už nie je možné viackrát zobraziť. V prípade straty je potrebné vytvoriť nový API kľúč.</DialogDescription>
        </DialogHeader>
        <div>
          <CopyInput v-if="recentlyCreatedToken" :value="recentlyCreatedToken" />
        </div>
        <DialogFooter>
          <Button @click="showTokenDialog.deactivate" variant="outline">Zatvoriť</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/Components/Button'
import { useConfirmable } from '@/Components/ConfirmationDialog'
import { CopyInput } from '@/Components/CopyInput'
import { DatePicker } from '@/Components/DatePicker'
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
import {
  Empty,
  EmptyContent,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle
} from '@/Components/Empty'
import { FormControl } from '@/Components/Form'
import HeadingSmall from '@/Components/HeadingSmall.vue'
import { Input } from '@/Components/Input'
import { useFlash } from '@/Composables/useFlash.ts'
import AppLayout from '@/Layouts/AppLayout.vue'
import { SettingsLayout } from '@/Layouts'
import { Head, useForm } from '@inertiajs/vue3'
import { asyncRouter, onActivated, onDeactivated, useToggle } from '@stacktrace/ui'
import { PlusIcon, Trash2Icon, KeySquareIcon, EllipsisVerticalIcon } from 'lucide-vue-next'
import { ref } from 'vue'

defineProps<{
  apiKeys: Array<{
    id: number
    name: string
    expiresAt: string | null
  }>
  minExpirationDate: string
  maxExpirationDate: string
}>()

const createApiKeyDialog = useToggle()
const form = useForm(() => ({
  name: '',
  expires_at: undefined,
}))
onActivated(createApiKeyDialog, () => {
  form.reset()
})
const create = () => {
  form.post(route('api-keys.store'), {
    onSuccess: () => {
      createApiKeyDialog.deactivate()
    }
  })
}

const showTokenDialog = useToggle()
const recentlyCreatedToken = ref<string>()
const showRecentlyCreatedToken = (token: string) => {
  recentlyCreatedToken.value = token
  showTokenDialog.activate()
}
onDeactivated(showTokenDialog, () => {
  setTimeout(() => {
    recentlyCreatedToken.value = undefined
  }, 300)
})
useFlash('recentlyCreatedApiKey', event => {
  showRecentlyCreatedToken(event.token)
})

const { confirm } = useConfirmable()
const confirmRevoke = (id: number) => confirm('Naozaj chcete odstrániť tento API kľúč? Všetky služby, ktoré používajú tento kľúč prestanú fungovať.', async () => {
  await asyncRouter.delete(route('api-keys.destroy', id))
}, {
  destructive: true,
  title: 'Odstrániť API kľúč',
  confirmLabel: 'Odstrániť',
  cancelLabel: 'Ponechať',
})
</script>
