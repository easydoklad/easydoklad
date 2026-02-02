<template>
  <AppLayout>
    <Head title="Vizuálna identita"/>

    <SettingsLayout>
      <section class="space-y-6">
        <HeadingSmall title="Vizuálna identita" description="Prispôsobte si vzhľad aplikácie a dokladov vlastným logom a farbami."/>

        <form @submit.prevent="save" class="space-y-6">
          <div class="grid grid-cols-2 gap-6">
            <FormControl label="Široké logo" help="Určené pre horizontálne rozloženia, ako sú hlavičky dokumentov alebo e-mailov. Odporúčame priehľadné PNG alebo biele pozadie. Rozmery strán: 100 px až 400 px, maximálne 8 MB." help-variant="tooltip">
              <TemporaryFileInput
                class="h-40"
                drop-class="h-40"
                :show-icon="false"
                :error="form.errors.wide_logo || form.errors.remove_wide_logo"
                scope="BrandingWideLogo"
                :source="wideLogoUrl"
                v-model:file="form.wide_logo"
                v-model:remove="form.remove_wide_logo"
              />
            </FormControl>

            <FormControl label="Štvorcové logo" help="Určené pre štvorcové formáty, napríklad profilové ikony alebo menšie grafické prvky. Vyžaduje sa pomer strán 1:1, ideálne ako priehľadné PNG alebo na bielom pozadí. Rozmery strán: 100 px až 400 px, maximálne 8 MB." help-variant="tooltip">
              <TemporaryFileInput
                class="h-40"
                drop-class="h-40 w-60"
                :show-icon="false"
                :error="form.errors.square_logo || form.errors.remove_square_logo"
                scope="BrandingSquareLogo"
                :source="squareLogoUrl"
                v-model:file="form.square_logo"
                v-model:remove="form.remove_square_logo"
              />
            </FormControl>
          </div>

          <Button type="submit" :recently-successful="form.recentlySuccessful" :processing="form.processing">Uložiť</Button>
        </form>
      </section>
    </SettingsLayout>
  </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/Components/Button'
import { FormControl } from '@/Components/Form'
import HeadingSmall from '@/Components/HeadingSmall.vue'
import { TemporaryFileInput } from '@/Components/TemporaryFileInput'
import { AppLayout, SettingsLayout } from '@/Layouts'
import { Head, useForm } from '@inertiajs/vue3'

defineProps<{
  squareLogoUrl: string | null
  wideLogoUrl: string | null
}>()

const form = useForm(() => ({
  square_logo: null,
  remove_square_logo: false,
  wide_logo: null,
  remove_wide_logo: false,
}))

const save = () => {
  form.patch(route('branding.update'))
}
</script>
