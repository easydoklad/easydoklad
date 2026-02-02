<template>
  <Dialog :control="control">
    <DialogContent @interact-outside.prevent>
      <DialogHeader>
        <DialogTitle>Odoslať faktúru</DialogTitle>
        <DialogDescription>Zadajte emailovú adresu na ktorú bude táto faktúra odoslaná</DialogDescription>
      </DialogHeader>
      <div class="flex flex-col gap-4">
        <FormControl label="E-Mail" :error="form.errors.email">
          <Input v-model="form.email" />
        </FormControl>

        <FormControl label="Jazyk" :error="form.errors.locale">
          <FormSelect :options="locales" v-model="form.locale" />
        </FormControl>

        <CheckboxControl v-model="customMessage">Poslať vlastnú správu</CheckboxControl>

        <FormControl v-if="customMessage" label="Správa" required :error="form.errors.message">
          <Textarea v-model="form.message" rows="6" />

          <div class="inline-flex flex-row mb-2">
            <Button type="button" @click="markdownHelp.activate" variant="link" class="text-muted-foreground px-0 text-xs" plain>Ako formátovať text?</Button>
            <DotIcon class="size-4 mt-[10px]" />
            <Button type="button" @click="replacementsHelp.activate" variant="link" class="text-muted-foreground px-0 text-xs" plain>Dynamické premenné</Button>
          </div>
        </FormControl>
      </div>
      <DialogFooter>
        <Button @click="control.deactivate" variant="outline">Zrušiť</Button>
        <Button :processing="form.processing" @click="send">Odoslať</Button>
      </DialogFooter>
    </DialogContent>

    <MarkdownHelpDialog :control="markdownHelp" />
    <ReplacementsHelpDialog :control="replacementsHelp" :replacements="[]" />
  </Dialog>
</template>

<script setup lang="ts">
import { CheckboxControl } from '@/Components/Checkbox'
import { Dialog, DialogTitle, DialogHeader, DialogFooter, DialogContent, DialogDescription } from '@/Components/Dialog'
import { Button } from '@/Components/Button'
import { FormControl, FormSelect } from "@/Components/Form"
import { MarkdownHelpDialog, ReplacementsHelpDialog } from '@/Components/Help'
import { Input } from "@/Components/Input"
import { Textarea } from "@/Components/Textarea"
import { useForm, usePage } from "@inertiajs/vue3"
import { onActivated, type Toggle, useToggle } from "@stacktrace/ui"
import { DotIcon } from 'lucide-vue-next'
import { computed, ref } from 'vue'

const props = defineProps<{
  control: Toggle
  id: string
  email?: string
}>()

const customMessage = ref(false)

const page = usePage()

const locales = computed(() => page.props.locales.map(it =>({ label: it.name, value: it.code })))

const form = useForm(() => ({
  email: props.email || '',
  locale: page.props.locale,
  message: '',
}))

onActivated(props.control, () => {
  form.clearErrors()
  form.reset()
  customMessage.value = false
})

const send = () => {
  form.transform(it => ({
    email: it.email,
    locale: it.locale,
    message: customMessage.value ? it.message : undefined,
  })).post(route('invoices.send', props.id), {
    onSuccess: () => {
      props.control.deactivate()
    },
    preserveScroll: true,
  })
}

const markdownHelp = useToggle()
const replacementsHelp = useToggle()
</script>
