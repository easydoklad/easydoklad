<template>
  <AuthBase title="Vytvoriť účet" description="Zadajte svoje údaje pre vytvorenie nového účtu">
    <Head title="Registrácia"/>

    <form @submit.prevent="submit" class="flex flex-col gap-6">
      <div class="grid gap-6">
        <FormControl for="name" label="Meno a priezvisko" :error="form.errors.name">
          <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" v-model="form.name"/>
        </FormControl>

        <FormControl for="email" label="E-Mail" :error="form.errors.email">
          <Input id="email" type="email" required :tabindex="2" autocomplete="email" v-model="form.email"/>
        </FormControl>

        <FormControl for="password" label="Heslo" :error="form.errors.password">
          <Input
            id="password"
            type="password"
            required
            :tabindex="3"
            autocomplete="new-password"
            v-model="form.password"
          />
        </FormControl>

        <FormControl for="password_confirmation" label="Potvrdenie hesla" :error="form.errors.password_confirmation">
          <Input
            id="password_confirmation"
            type="password"
            required
            :tabindex="4"
            autocomplete="new-password"
            v-model="form.password_confirmation"
          />
        </FormControl>

        <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="form.processing" :processing="form.processing">
          Vytvoriť účet
        </Button>
      </div>

      <div class="text-center text-sm text-muted-foreground">
        Máte už účet?
        <TextLink :href="route('login')" class="underline underline-offset-4" :tabindex="6">Prihláste sa</TextLink>
      </div>
    </form>
  </AuthBase>
</template>

<script setup lang="ts">
import { FormControl } from '@/Components/Form'
import TextLink from '@/Components/TextLink.vue';
import { Button } from '@/Components/Button';
import { Input } from '@/Components/Input';
import AuthBase from '@/Layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const submit = () => {
  form.post(route('register'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  });
};
</script>
