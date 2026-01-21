<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Nastavenia hesla"/>

    <UserSettingsLayout>
      <div class="space-y-6">
        <HeadingSmall title="Aktualizácia hesla" description="Uistite sa, že používate dlhé, náhodné a bezpečné heslo"/>

        <form @submit.prevent="updatePassword" class="space-y-6">
          <FormControl for="current_password" label="Aktuálne heslo" :error="form.errors.current_password">
            <Input
              id="current_password"
              ref="currentPasswordInput"
              v-model="form.current_password"
              type="password"
              class="mt-1 block w-full"
              autocomplete="current-password"
            />
          </FormControl>

          <FormControl for="password" label="Nové heslo" :error="form.errors.password">
            <Input
              id="password"
              ref="passwordInput"
              v-model="form.password"
              type="password"
              class="mt-1 block w-full"
              autocomplete="new-password"
            />
          </FormControl>

          <FormControl for="password_confirmation" label="Potvrdenie nového hesla" :error="form.errors.password_confirmation">
            <Input
              id="password_confirmation"
              v-model="form.password_confirmation"
              type="password"
              class="mt-1 block w-full"
              autocomplete="new-password"
            />
          </FormControl>


          <div class="flex items-center gap-4">
            <Button :disabled="form.processing" :processing="form.processing" :recently-successful="form.recentlySuccessful">Uložiť heslo</Button>
          </div>
        </form>
      </div>
    </UserSettingsLayout>
  </AppLayout>
</template>

<script setup lang="ts">
import { FormControl } from '@/Components/Form'
import AppLayout from '@/Layouts/AppLayout.vue';
import UserSettingsLayout from '@/Layouts/UserSettingsLayout.vue'
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

import HeadingSmall from '@/Components/HeadingSmall.vue';
import { Button } from '@/Components/Button';
import { Input } from '@/Components/Input';
import { type BreadcrumbItem } from '@/Types';

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Password settings',
    href: '/settings/password',
  },
];

const passwordInput = ref<HTMLInputElement | null>(null);
const currentPasswordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
});

const updatePassword = () => {
  form.put(route('password.update'), {
    preserveScroll: true,
    onSuccess: () => form.reset(),
    onError: (errors: any) => {
      if (errors.password) {
        form.reset('password', 'password_confirmation');
        if (passwordInput.value instanceof HTMLInputElement) {
          passwordInput.value.focus();
        }
      }

      if (errors.current_password) {
        form.reset('current_password');
        if (currentPasswordInput.value instanceof HTMLInputElement) {
          currentPasswordInput.value.focus();
        }
      }
    },
  });
};
</script>
