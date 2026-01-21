<template>
  <AuthBase title="Prihláste sa do svojho účtu" description="Zadajte email a heslo nižšie pre prihlásenie">
    <Head title="Prihlásenie"/>

    <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit" class="flex flex-col gap-6">
      <div class="grid gap-6">
        <FormControl for="email" label="E-Mail" :error="form.errors.email || errors?.email">
          <Input
            id="email"
            type="email"
            required
            autofocus
            :tabindex="1"
            autocomplete="email"
            v-model="form.email"
            placeholder="email@example.com"
          />
        </FormControl>

        <FormControl for="password" label="Heslo" :error="form.errors.password">
          <Input
            id="password"
            type="password"
            required
            :tabindex="2"
            autocomplete="current-password"
            v-model="form.password"
            placeholder="Password"
          />

          <TextLink v-if="canResetPassword" :href="route('password.request')" class="block text-sm mt-4" :tabindex="5">
            Zabudli ste heslo?
          </TextLink>
        </FormControl>

        <div class="flex items-center justify-between">
          <Label for="remember" class="flex items-center space-x-3">
            <Checkbox id="remember" v-model="form.remember" :tabindex="3"/>
            <span>Zapamätať si prihlásenie</span>
          </Label>
        </div>

        <Button type="submit" class="mt-4 w-full" :tabindex="4" :disabled="form.processing" :processing="form.processing">
          Prihlásiť
        </Button>
      </div>

      <div class="text-center text-sm text-muted-foreground">
        Ešte nemáte účet?
        <TextLink :href="route('register')" :tabindex="6">Registrujte sa</TextLink>
      </div>
    </form>
  </AuthBase>
</template>

<script setup lang="ts">
import { FormControl } from '@/Components/Form'
import TextLink from '@/Components/TextLink.vue';
import { Button } from '@/Components/Button';
import { Checkbox } from '@/Components/Checkbox';
import { Input } from '@/Components/Input';
import { Label } from '@/Components/Label';
import AuthBase from '@/Layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
  status?: string;
  canResetPassword: boolean;
  errors?: Record<string, string>
}>();

const form = useForm({
  email: '',
  password: '',
  remember: false,
});

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  });
};
</script>
