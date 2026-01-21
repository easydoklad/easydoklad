<template>
  <div class="px-4 py-6">
    <Heading
      title="Môj účet"
      description="Vaše prihlasovacie údaje, e-mail a nastavenia zabezpečenia."
    />

    <div
      class="flex flex-col space-y-8 md:space-y-0 lg:flex-row lg:space-y-0 lg:space-x-12"
    >
      <aside class="w-full max-w-xl lg:w-48 gap-8 flex flex-col">
        <TabsNavigation
          :menu="[
            {
              title: 'Osobné údaje',
              action: { route: 'profile.edit' },
            },
            {
              title: 'Heslo',
              action: { route: 'password.edit' },
            },
            {
              title: 'Vzhľad',
              action: { route: 'appearance' },
            },
          ]"
          orientation="vertical"
          variant="ghost"
        />
      </aside>

      <Separator class="my-6 md:hidden" />

      <div class="flex-1 md:max-w-2xl">
        <section class="max-w-xl space-y-12">
          <slot />
        </section>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import Heading from "@/Components/Heading.vue";
import { Separator } from "@/Components/Separator";
import { TabsNavigation } from "@/Components/Tabs";
</script>
