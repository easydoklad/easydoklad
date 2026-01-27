<template>
  <AppLayout>
    <Head title="Používatelia"/>

    <SettingsLayout>
      <section class="space-y-6">
        <HeadingSmall title="Používatelia" description="Spravujte používateľov, ktorí majú prístup k vašej firme."/>

        <template v-if="users.length > 0">
          <div class="flex flex-col divide-y">
            <div v-for="user in users" class="flex flex-row py-2 items-center">
              <div class="flex flex-col">
                <p class="text-sm font-medium">{{ user.name }}</p>
                <p class="text-xs text-muted-foreground">{{ user.email }}</p>

              </div>

              <div class="inline-flex flex-row items-center ml-auto">
                <div class="inline-flex flex-row items-center mr-2">
                  <Badge v-if="user.role === 'owner'">Majiteľ</Badge>
                  <Badge v-else-if="user.role === 'user'" variant="secondary">Používateľ</Badge>
                </div>

                <DropdownMenu>
                  <DropdownMenuTrigger as-child :disabled="!user.can.delete && !user.can.update">
                    <Button variant="ghost" size="icon">
                      <EllipsisVerticalIcon />
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuItem @select="edit(user)"><EditIcon /> Upraviť</DropdownMenuItem>
                    <DropdownMenuSeparator v-if="user.can.delete && user.can.update" />
                    <DropdownMenuItem variant="destructive" @select="destroy(user)"><Trash2Icon /> Odstrániť</DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </div>
          </div>

          <Button v-if="can.invite" @click="inviteDialog.activate" size="sm" variant="outline" :icon="UserPlusIcon" label="Pozvať používateľa" />
        </template>
      </section>

      <section v-if="invitations.length > 0" class="space-y-6">
        <HeadingSmall title="Pozvánky" :description="`Pozvaní používatelia musia akceptovať pozvánku do ${expirationHours} hodín od odoslania. V prípade exspirácie môžete pozvánku odoslať znovu.`" />

        <div class="flex flex-col divide-y">
          <div v-for="invitation in invitations" class="flex flex-row py-2 items-center">
            <div class="flex flex-col">
              <p class="text-sm font-medium">{{ invitation.email }}</p>
              <p class="text-xs text-muted-foreground">{{ invitation.role }}</p>
            </div>

            <div class="inline-flex flex-row items-center ml-auto">
              <div class="inline-flex flex-row items-center mr-2">
                <Badge v-if="invitation.expired" variant="destructive"><TriangleAlertIcon /> Exspirovaná</Badge>
                <Badge v-else variant="warning"><ClockIcon /> Odoslaná</Badge>
              </div>

              <DropdownMenu>
                <DropdownMenuTrigger as-child :disabled="!invitation.can.revoke && !invitation.can.resend">
                  <Button variant="ghost" size="icon">
                    <EllipsisVerticalIcon />
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  <DropdownMenuItem @select="resendInvitation(invitation)"><SendIcon /> Odoslať znova</DropdownMenuItem>
                  <DropdownMenuSeparator v-if="invitation.can.revoke && invitation.can.resend" />
                  <DropdownMenuItem variant="destructive" @select="revokeInvitation(invitation)"><Trash2Icon /> Odstrániť</DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>
          </div>
        </div>
      </section>
    </SettingsLayout>

    <Dialog :control="inviteDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Pozvať používateľa</DialogTitle>
          <DialogDescription>Poskytnite inému používateľovi prístup k vašej firme.</DialogDescription>
        </DialogHeader>
        <div class="flex flex-col gap-4 mb-2">
          <FormControl label="E-Mail" :error="inviteForm.errors.email">
            <Input v-model="inviteForm.email" />
          </FormControl>

          <FormControl label="Prístup" :error="inviteForm.errors.role">
            <FormSelect :options="roles" v-model="inviteForm.role" />
          </FormControl>
        </div>
        <DialogFooter>
          <Button @click="inviteDialog.deactivate" variant="outline">Zrušiť</Button>
          <Button :processing="inviteForm.processing" @click="invite">Pozvať</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Dialog :control="editDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Upraviť používateľa</DialogTitle>
        </DialogHeader>
        <div class="flex flex-col mb-2">
          <FormControl label="Prístup" :error="editForm.errors.role">
            <FormSelect :options="roles" v-model="editForm.role" />
          </FormControl>
        </div>
        <DialogFooter>
          <Button variant="outline">Zrušiť</Button>
          <Button :processing="editForm.processing" @click="saveUser">Uložiť</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>

<script setup lang="ts">
import { Badge } from '@/Components/Badge'
import { Button } from '@/Components/Button'
import { useConfirmable } from '@/Components/ConfirmationDialog'
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
  DropdownMenuSeparator, DropdownMenuTrigger
} from '@/Components/DropdownMenu'
import { FormControl, FormSelect } from '@/Components/Form'
import HeadingSmall from '@/Components/HeadingSmall.vue'
import { Input } from '@/Components/Input'
import { AppLayout, SettingsLayout } from '@/Layouts'
import { Head, useForm } from '@inertiajs/vue3'
import { asyncRouter, onActivated, type SelectOption, useToggle } from '@stacktrace/ui'
import { EditIcon, EllipsisVerticalIcon, UserPlusIcon, Trash2Icon, SendIcon, ClockIcon, TriangleAlertIcon } from 'lucide-vue-next'
import { ref } from 'vue'

interface User {
  id: string
  name: string
  email: string
  role: 'owner' | 'user'
  can: {
    update: boolean
    delete: boolean
  }
}

interface Invitation {
  id: string
  email: string
  role: string
  expired: boolean
  can: {
    revoke: boolean
    resend: boolean
  }
}

defineProps<{
  users: Array<User>
  invitations: Array<Invitation>
  can: {
    invite: boolean
  }
  roles: Array<SelectOption>
  expirationHours: number
}>()

const { confirm, confirmDestructive } = useConfirmable()

const editDialog = useToggle()
const editedUser = ref<User>()
const editForm = useForm(() => ({
  role: editedUser.value?.role || 'user',
}))
const edit = (user: User) => {
  editedUser.value = user
  editForm.reset()
  editDialog.activate()
}
const saveUser = () => {
  const user = editedUser.value
  if (user) {
    editForm.patch(route('users.update', user.id), {
      onSuccess: () => {
        editDialog.deactivate()
      }
    })
  }
}

const destroy = (user: User) => confirmDestructive('Skutočne chcete odstrániť vybraného používateľa? Používate už viac nebude mať prístup k vašej firme.', async () => {
  await asyncRouter.delete(route('users.destroy', user.id))
})

const inviteDialog = useToggle()
const inviteForm = useForm(() => ({
  email: '',
  role: 'user',
}))
onActivated(inviteDialog, () => inviteForm.resetAndClearErrors())
const invite = () => {
  inviteForm.post(route('user-invitations.store'), {
    onSuccess: () => {
      inviteDialog.deactivate()
    }
  })
}

const resendInvitation = (invitation: Invitation) => confirm('Chcete odoslať pozvánku ešte raz?', async () => {
  await asyncRouter.post(route('user-invitations.resend', invitation.id))
})

const revokeInvitation = (invitation: Invitation) => confirmDestructive('Naozaj chcete odstrániť túto pozvánku?', async () => {
  await asyncRouter.delete(route('user-invitations.destroy', invitation.id))
})
</script>
