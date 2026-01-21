<template>
  <AppLayout>
    <Head title="Webhooky"/>

    <SettingsLayout>
      <section class="space-y-6">
        <HeadingSmall title="Webhooky" description="Webhooky slúžia na informovanie systémov tretích strán o udalostiach v easyDoklade."/>

        <Empty v-if="webhooks.length === 0">
          <EmptyHeader>
            <EmptyMedia variant="icon">
              <WebhookIcon />
            </EmptyMedia>
            <EmptyTitle>Žiadne Webhooky.</EmptyTitle>
            <EmptyDescription>Zatiaľ neboli vytvorené žiadne Webhooky.</EmptyDescription>
          </EmptyHeader>
          <EmptyContent>
            <Button @click="create" size="sm" variant="outline" :icon="PlusIcon" label="Pridať Webhook" />
          </EmptyContent>
        </Empty>

        <template v-else>
          <div class="flex flex-col divide-y">
            <div v-for="webhook in webhooks" class="flex flex-row py-2 items-center">
              <div class="flex flex-col">
                <p class="text-sm font-medium">{{ webhook.name }}</p>
                <p class="text-xs text-muted-foreground">{{ webhook.url }}</p>
                <div class="inline-flex flex-row items-center mt-1.5 gap-1">
                  <Badge variant="secondary">{{ pluralize(':count udalosť|:count udalosti|:count udalostí', webhook.events.length) }}</Badge>
                  <Badge v-if="webhook.active" variant="positive" class="border-transparent">Aktívny</Badge>
                  <Badge v-else variant="destructive" class="border-transparent">Neaktívny</Badge>
                </div>
              </div>

              <div class="inline-flex flex-row items-center ml-auto">
                <ToggleSwitch class="mr-4" :value="webhook.active" :url="route('webhooks.toggle-active', webhook.id)" field="active" />

                <Button @click="showSecret(webhook)" variant="ghost" size="icon">
                  <KeyRoundIcon />
                </Button>

                <DropdownMenu>
                  <DropdownMenuTrigger as-child>
                    <Button variant="ghost" size="icon">
                      <EllipsisVerticalIcon />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuItem @select="edit(webhook)"><EditIcon /> Upraviť</DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem variant="destructive" @select="destroy(webhook)"><Trash2Icon /> Odstrániť</DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </div>
          </div>

          <Button @click="create" size="sm" variant="outline" :icon="PlusIcon" label="Pridať Webhook" />
        </template>
      </section>
    </SettingsLayout>

    <Dialog :control="dialog">
      <DialogScrollContent>
        <DialogHeader>
          <DialogTitle>{{ isEditing ? 'Upraviť Webhook' : 'Nový Webhook' }}</DialogTitle>
        </DialogHeader>
        <div class="flex flex-col gap-4">
          <FormControl label="Názov" :error="form.errors.name">
            <Input v-model="form.name" />
          </FormControl>

          <FormControl label="URL Adresa" :error="form.errors.url">
            <Input v-model="form.url" />
          </FormControl>

          <FormControl label="Udalosti" :error="form.errors.events">
            <Accordion collapsible class="border rounded-md shadow-xs overflow-hidden">
              <AccordionItem v-for="group in events" :value="group.group">
                <AccordionTrigger class="hover:no-underline hover:cursor-pointer hover:bg-secondary rounded-none hover:text-secondary-foreground px-3 py-3">
                  <div class="flex flex-row justify-between w-full">
                    {{ group.group }}

                    <Badge :variant="getSelectedCount(group.events) > 0 ? 'default' : 'secondary'">vybrané {{ getSelectedCount(group.events) }} / {{ group.events.length }}</Badge>
                  </div>
                </AccordionTrigger>
                <AccordionContent class="pb-0 border-t border-dashed">
                  <div class="divide-y divide-dashed">
                    <div v-for="event in group.events" class="py-2 flex flex-row px-4">
                      <Checkbox :id="event.id" v-model="form.events" :value="event.id" class="mr-2 mt-1" />

                      <label :for="event.id" class="flex flex-col cursor-pointer">
                        <p class="font-mono font-medium text-sm mb-0.5">{{ event.id }}</p>
                        <p class="text-muted-foreground text-xs">{{ event.description }}</p>
                      </label>
                    </div>
                  </div>
                </AccordionContent>
              </AccordionItem>
            </Accordion>
          </FormControl>
        </div>
        <DialogFooter>
          <Button @click="dialog.deactivate" variant="outline">Zrušiť</Button>
          <Button @click="save" :processing="form.processing">{{ isEditing ? 'Uložiť' : 'Pridať' }}</Button>
        </DialogFooter>
      </DialogScrollContent>
    </Dialog>

    <Dialog :control="secretDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Podpisový kľúč</DialogTitle>
          <DialogDescription>Všetky odoslané webhooky sú podpísané týmto kľúčom. Pre zvýšenie bezpečnosti integrácie odporúčame vždy overiť podpis webhooku týmto kľúčom.</DialogDescription>
        </DialogHeader>
        <CopyInput v-if="secretDialogWebhook" :value="secretDialogWebhook.secret" />
        <DialogFooter>
          <Button @click="secretDialog.deactivate" variant="outline">Zatvoriť</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/Components/Accordion'
import { Button } from '@/Components/Button'
import { Badge } from '@/Components/Badge'
import { Checkbox } from '@/Components/Checkbox'
import { useConfirmable } from '@/Components/ConfirmationDialog'
import { CopyInput } from '@/Components/CopyInput'
import {
  Dialog, DialogContent, DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogScrollContent,
  DialogTitle
} from '@/Components/Dialog'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem, DropdownMenuSeparator,
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
import { ToggleSwitch } from '@/Components/Switch'
import { useResourceFormDialog } from '@/Composables/useResourceFormDialog.ts'
import AppLayout from '@/Layouts/AppLayout.vue'
import { SettingsLayout } from '@/Layouts'
import { pluralize } from '@/Utils'
import { Head } from '@inertiajs/vue3'
import { asyncRouter, useToggle } from '@stacktrace/ui'
import { PlusIcon, Trash2Icon, WebhookIcon, EditIcon, EllipsisVerticalIcon, KeyRoundIcon } from 'lucide-vue-next'
import { ref } from 'vue'

interface Webhook {
  id: string
  name: string
  events: Array<string>
  url: string
  active: boolean
  secret: string
}

interface WebhookEvent {
  id: string
  description: string
}

defineProps<{
  events: Array<{
    group: string
    events: Array<WebhookEvent>
  }>
  webhooks: Array<Webhook>
}>()

const { confirmDestructive } = useConfirmable()

const { create, dialog, isEditing, save, form, edit } = useResourceFormDialog((webhook?: Webhook) => ({
  name: webhook?.name || '',
  events: webhook ? webhook.events : [],
  url: webhook?.url || '',
}), {
  create: () => route('webhooks.store'),
  update: webhook => route('webhooks.update', webhook.id)
})

const getSelectedCount = (events: Array<WebhookEvent>) => events.filter(it => form.events.includes(it.id)).length

const destroy = (webhook: Webhook) => confirmDestructive('Skutočne chcete odstrániť tento webhook?', async () => {
  await asyncRouter.delete(route('webhooks.destroy', webhook.id))
})

const secretDialog = useToggle()
const secretDialogWebhook = ref<Webhook>()

const showSecret = (webhook: Webhook) => {
  secretDialogWebhook.value = webhook
  secretDialog.activate()
}
</script>
