<template>
  <div>
    <Head :title="title" />

    <Empty v-if="status === 'accepted'">
      <EmptyHeader>
        <EmptyMedia variant="icon">
          <CheckIcon />
        </EmptyMedia>
        <EmptyTitle>Pozvánka prijatá</EmptyTitle>
        <EmptyDescription>Táto pozvánka už bola akceptovaná.</EmptyDescription>
      </EmptyHeader>
      <EmptyContent>
        <LinkButton :href="route('home')">Pokračovať</LinkButton>
      </EmptyContent>
    </Empty>

    <Empty v-else-if="status === 'invalid' || status === 'expired'">
      <EmptyHeader>
        <EmptyMedia variant="icon">
          <XIcon />
        </EmptyMedia>
        <EmptyTitle>Neplatná pozvánka</EmptyTitle>
        <EmptyDescription v-if="status === 'invalid'">Táto pozvánka nie je platná.</EmptyDescription>
        <EmptyDescription v-else-if="status === 'expired'">Platnosť tejto pozvánky vypršala.</EmptyDescription>
      </EmptyHeader>
      <EmptyContent>
        <LinkButton :href="route('home')">OK</LinkButton>
      </EmptyContent>
    </Empty>

    <Empty v-else-if="status === 'duplicate'">
      <EmptyHeader>
        <EmptyMedia variant="icon">
          <XIcon />
        </EmptyMedia>
        <EmptyTitle>Neplatná pozvánka</EmptyTitle>
        <EmptyDescription>K tejto firme už máte prístup. Ak si želáte pozvánku prijať, musíte sa prihlásiť k inému účtu.</EmptyDescription>
      </EmptyHeader>
      <EmptyContent>
        <LinkButton :href="route('home')">Rozumiem</LinkButton>

        <LinkButton method="post" :href="route('logout')" variant="link">Odhlásiť sa</LinkButton>
      </EmptyContent>
    </Empty>

    <div v-if="invitation" class="flex items-center justify-center p-12">
      <div class="max-w-lg w-full flex flex-col items-center">
        <h3 class="text-lg font-medium tracking-tight text-center">Pripojte sa k firme {{ invitation.account }}</h3>
        <p class="text-muted-foreground text-sm/relaxed text-center mt-2">
          Prijatím tejto pozvánky získate prístup k firme {{ invitation.account }}.
        </p>

        <Tabs v-if="invitation.guest" default-value="create-account" class="w-full mt-8">
          <TabsList class="flex w-full mb-4">
            <TabsTrigger value="create-account" class="flex-1 justify-center">Vytvoriť nový účet</TabsTrigger>
            <TabsTrigger value="connect-account" class="flex-1 justify-center">Pripojiť k existujúcemu účtu</TabsTrigger>
          </TabsList>

          <TabsContent value="create-account">
            <p class="text-muted-foreground text-sm/relaxed text-center">
              Ak ešte nemáte účet v službe easyDoklad, teraz je správny čas na vytvorenie. Po vytvorení účtu získate prístup k firme <strong>{{ invitation.account }}</strong>.
            </p>

            <form @submit.prevent="createAccount" class="flex flex-col gap-6 mt-6">
              <div class="grid gap-6">
                <FormControl for="name" label="Meno a priezvisko" :error="createAccountForm.errors.name">
                  <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="name" v-model="createAccountForm.name"/>
                </FormControl>

                <FormControl for="email" label="E-Mail" :error="createAccountForm.errors.email">
                  <Input id="email" type="email" required :tabindex="2" autocomplete="email" v-model="createAccountForm.email"/>
                </FormControl>

                <FormControl for="password" label="Heslo" :error="createAccountForm.errors.password">
                  <Input
                    id="password"
                    type="password"
                    required
                    :tabindex="3"
                    autocomplete="new-password"
                    v-model="createAccountForm.password"
                  />
                </FormControl>

                <FormControl for="password_confirmation" label="Potvrdenie hesla" :error="createAccountForm.errors.password_confirmation">
                  <Input
                    id="password_confirmation"
                    type="password"
                    required
                    :tabindex="4"
                    autocomplete="new-password"
                    v-model="createAccountForm.password_confirmation"
                  />
                </FormControl>

                <Button type="submit" class="mt-2 w-full" tabindex="5" :disabled="createAccountForm.processing" :processing="createAccountForm.processing">
                  Akceptovať pozvánku
                </Button>

                <p class="text-muted-foreground text-xs text-center">
                  Kliknutím na "Akceptovať pozvánku" bude vytvorený nový účet a pozvánka bude automaticky akceptovaná pod novo vytvoreným účtom.
                </p>
              </div>
            </form>
          </TabsContent>

          <TabsContent value="connect-account" class="flex flex-col">
            <p class="text-muted-foreground text-sm/relaxed text-center">
              Ak už účet v službe easyDoklad máte, pripojte si firmu <strong>{{ invitation.account }}</strong> k vášmu existujúcemu účtu.
            </p>

            <LinkButton class="w-full max-w-xs mx-auto mt-4" :href="route('login', { _query: { intended: $page.url } })">Prihlásiť sa</LinkButton>
          </TabsContent>
        </Tabs>

        <div v-else class="mt-4 w-full flex flex-col items-center">
          <Button @click="accept" :processing="acceptForm.processing">Akceptovať pozvánku</Button>

          <p class="text-muted-foreground text-xs text-center mt-4">
            Kliknutím na "Akceptovať pozvánku" bude k vášmu účtu {{ user?.name }} pripojená firma {{ invitation.account }}.
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Button, LinkButton } from '@/Components/Button'
import {
  Empty,
  EmptyContent,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle
} from '@/Components/Empty'
import { FormControl } from '@/Components/Form'
import { Input } from '@/Components/Input'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/Tabs'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { XIcon, CheckIcon } from 'lucide-vue-next'
import { computed } from 'vue'

const props = defineProps<{
  status: 'invalid' | 'pending' | 'accepted' | 'expired' | 'duplicate'
  invitation?: {
    token: string
    account: string
    guest: boolean
  }
}>()

const page = usePage()
const user = computed(() => page.props.auth.user)

const title = computed(() => ({
  'invalid': 'Neplatná pozvánka',
  'pending': 'Akceptovať pozvánku',
  'accepted': 'Pozvánka akceptovaná',
  'expired': 'Neplatná pozvánka',
  'duplicate': 'Neplatná pozvánka',
}[props.status]))

const createAccountForm = useForm(() => ({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
}))
const createAccount = () => {
  createAccountForm
    .transform(it => ({ ...it, invitation: props.invitation?.token }))
    .post(route('register'))
}

const acceptForm = useForm(() => ({}))
const accept = () => {
  acceptForm.post(route('accept-invitation.store', props.invitation?.token))
}
</script>
