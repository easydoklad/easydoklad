<template>
  <FormControl :error="error || errorMessage">
    <Dropzone
      :class="dropClass"
      v-if="showDropZone"
      @files="onFiles"
      :processing="uploading"
      :disabled="disabled"
      :show-icon="showIcon"
    />

    <template v-else>
      <div
        v-if="image"
        :class="cn(
          'relative w-fit flex items-center justify-center border border-dashed p-2 rounded-md',
          $attrs.class || ''
        )"
      >
        <img class="h-32 object-contain" v-if="preview" :src="preview" alt="">

        <TooltipProvider :delay-duration="0" v-if="! disabled">
          <Tooltip @click.stop>
            <TooltipTrigger as-child>
              <button class="text-destructive absolute -top-4 -right-4 opacity-70 hover:opacity-100 p-2" @click="remove">
                <XCircleIcon class="size-4" />
              </button>
            </TooltipTrigger>
            <TooltipContent>Odstrániť</TooltipContent>
          </Tooltip>
        </TooltipProvider>
      </div>

      <div
        v-else
        :class="cn(
          'border-2 border-dashed border-input flex flex-row items-center justify-between gap-4 rounded-md p-3',
          $attrs.class || '',
        )"
      >
        <p class="break-all text-sm font-medium">{{ uploadedFile?.client_name}}</p>

        <TooltipProvider :delay-duration="0" v-if="! disabled">
          <Tooltip @click.stop>
            <TooltipTrigger as-child>
              <button class="text-destructive opacity-70 hover:opacity-100 p-2" @click="remove">
                <XCircleIcon class="size-4" />
              </button>
            </TooltipTrigger>
            <TooltipContent>Odstrániť</TooltipContent>
          </Tooltip>
        </TooltipProvider>
      </div>
    </template>

  </FormControl>
</template>

<script setup lang="ts">
import { Dropzone } from '@/Components/Dropzone'
import { FormControl } from "@/Components/Form";
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from "@/Components/Tooltip";
import { cn } from "@/Utils";
import axios from 'axios'
import { XCircleIcon } from "lucide-vue-next";
import { computed, type HTMLAttributes, ref, watch } from "vue";

const emit = defineEmits(['update:file', 'update:remove'])

const props = withDefaults(defineProps<{
  error?: string | null | undefined
  scope: string
  source?: string | null
  remove?: boolean
  file: string | null
  disabled?: boolean
  dropClass?: HTMLAttributes['class']
  showIcon?: boolean
  image?: boolean
}>(), {
  showIcon: true,
  image: true,
})

interface Upload {
  id: string
  url: string
  client_name: string
}

const uploading = ref(false)
const uploadedFile = ref<Upload>()
const errorMessage = ref<string>()

const hasOriginalFile = computed(() => !!props.source)
const hasPendingUpload = computed(() => !!uploadedFile.value)

const preview = computed(() => uploadedFile.value?.url || props.source)

const showDropZone = computed(() => {
  if (uploading.value || props.remove) {
    return true
  }

  if (hasOriginalFile.value || hasPendingUpload.value) {
    return false
  }

  return true
})

const onFiles = async (files: Array<File>) => {
  await upload(files[0])
}

const upload = async (file: File) => {
  const shouldRemove = !!props.remove

  emit('update:remove', false)
  emit('update:file', null)

  uploading.value = true
  uploadedFile.value = undefined
  errorMessage.value = undefined

  const formData = new FormData()
  formData.append('scope', props.scope)
  formData.append('file', file)

  try {
    const response = await axios.post<Upload>(route('files.store'), formData)

    uploadedFile.value = response.data

    onNewFileUploaded(response.data)
  } catch (e: any) {
    const message = e.response?.data?.message

    if (message) {
      errorMessage.value = message
    } else {
      errorMessage.value = 'Súbor sa nepodarilo nahrať.'
    }

    if (shouldRemove) {
      emit('update:remove', true)
    }
  }

  uploading.value = false
}

const onNewFileUploaded = (upload: Upload) => {
  emit('update:remove', false)
  emit('update:file', upload.id)
}

const remove = () => {
  if (hasOriginalFile.value) {
    emit('update:remove', true)
    emit('update:file', null)
  } else {
    emit('update:remove', false)
    emit('update:file', null)
  }

  uploadedFile.value = undefined
}

watch(hasOriginalFile, (value, oldValue) => {
  if (oldValue === false && value === true) {
    emit('update:file', null)
    emit('update:remove', false)
    uploadedFile.value = undefined
  }
})
</script>
