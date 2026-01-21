<template>
  <AppLayout :breadcrumbs="breadcrumbItems">
    <Head title="Vzhľad"/>

    <UserSettingsLayout>
      <div class="space-y-6">
        <HeadingSmall title="Nastavenie vzhľadu" description="Nastavte si preferovaný vzhľad aplikácie"/>
        <AppearanceTabs/>
      </div>
    </UserSettingsLayout>
  </AppLayout>
</template>

<script setup lang="ts">
import UserSettingsLayout from '@/Layouts/UserSettingsLayout.vue'
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/Components/AppearanceTabs.vue';
import HeadingSmall from '@/Components/HeadingSmall.vue';
import { type BreadcrumbItem } from '@/Types';

import AppLayout from '@/Layouts/AppLayout.vue';

const breadcrumbItems: BreadcrumbItem[] = [
  {
    title: 'Appearance settings',
    href: '/settings/appearance',
  },
];
</script>
