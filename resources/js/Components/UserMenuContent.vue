<template>
  <DropdownMenuItem as-child class="p-0 font-normal">
    <Link :href="route('profile.edit')" class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
      <UserInfo :user="user" :show-email="true"/>
    </Link>
  </DropdownMenuItem>
  <DropdownMenuSeparator/>
  <template v-if="showAccountNavigation">
    <DropdownMenuGroup>
      <DropdownMenuLabel>Firma</DropdownMenuLabel>
      <DropdownMenuCheckboxItem
        v-for="account in user.accounts"
        :model-value="account.current"
        class="pl-10"
        @select="account.current ? () => {} : switchAccount(account.id)"
      >{{ account.name }}</DropdownMenuCheckboxItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator/>
    <DropdownMenuItem as-child><Link :href="route('accounts.create')"><PlusIcon class="mr-2" /> Pridať firmu</Link></DropdownMenuItem>
    <DropdownMenuSeparator/>
    <DropdownMenuGroup>
      <DropdownMenuItem :as-child="true">
        <Link class="block w-full" :href="route('accounts.edit')">
          <Settings class="mr-2 h-4 w-4"/>
          Správa firmy
        </Link>
      </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator/>
  </template>
  <DropdownMenuItem :as-child="true">
    <Link class="block w-full" method="post" :href="route('logout')" @click="handleLogout" as="button">
      <LogOut class="mr-2 h-4 w-4"/>
      Odhlásiť sa
    </Link>
  </DropdownMenuItem>
</template>

<script setup lang="ts">
import UserInfo from '@/Components/UserInfo.vue';
import {
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuCheckboxItem,
  DropdownMenuSeparator
} from '@/Components/DropdownMenu';
import type { User } from '@/Types';
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings, PlusIcon } from 'lucide-vue-next';

defineProps<{
  user: User
  showAccountNavigation?: boolean
}>()

const handleLogout = () => router.flushAll()

const switchAccount = (id: number) => {
  router.post(route('accounts.switch', id), {}, {
    preserveState: false,
  })
}
</script>
