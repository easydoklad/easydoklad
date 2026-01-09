<template>
  <div class="border border-input rounded-md flex flex-row justify-between px-3 py-2 items-center gap-2">
    <div @click="copyToClipboard" class="select-all wrap-anywhere text-sm leading-tight font-mono">{{ value }}</div>

    <Button @click="copyToClipboard" variant="ghost" size="icon">
      <CopyIcon />
    </Button>
  </div>
</template>

<script setup lang="ts">
import { Button } from '@/Components/Button'
import { CopyIcon } from 'lucide-vue-next'
import { toast } from 'vue-sonner'
import { useClipboard } from '@vueuse/core'

const props = defineProps<{
  value: string
}>()

const { copy } = useClipboard({ legacy: true })

const copyToClipboard = () => {
  toast('Skopírované do schránky!')

  copy(props.value)
}
</script>
