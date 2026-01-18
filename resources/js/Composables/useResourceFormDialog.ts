import { useForm } from '@inertiajs/vue3'
import type { Page, FormDataType } from '@inertiajs/core'
import { onActivated, useToggle } from '@stacktrace/ui'
import { computed, ref } from 'vue'

interface ResourceFormDialogOptions<T> {
  update: (resource: T) => string
  create: () => string
  onCreated?: (page: Page) => void
  onUpdated?: (page: Page) => void
}

type ResourceValueCallback<T, V> = (resource: T | undefined) => V

export function useResourceFormDialog<Resource, FormValue extends FormDataType<FormValue>>(
  resolveValue: ResourceValueCallback<Resource, FormValue>,
  options: ResourceFormDialogOptions<Resource>
) {
  const dialog = useToggle()

  const valueUnderEditing = ref<Resource>()

  const form = useForm<FormValue>(() => resolveValue(valueUnderEditing.value || undefined))

  const isEditing = computed(() => !!valueUnderEditing.value)

  const save = () => {
    const resource = valueUnderEditing.value
    const url = resource ? options.update(resource) : options.create()
    const method = resource ? 'patch' : 'post'

    form.submit(method, url, {
      onSuccess: page => {
        dialog.deactivate()

        if (resource) {
          if (options.onUpdated) {
            options.onUpdated(page)
          }
        } else {
          if (options.onCreated) {
            options.onCreated(page)
          }
        }
      }
    })
  }

  const create = () => {
    valueUnderEditing.value = undefined
    dialog.activate()
  }

  const edit = (resource: Resource) => {
    valueUnderEditing.value = resource
    dialog.activate()
  }

  onActivated(dialog, () =>{
    form.reset()
    form.clearErrors()
  })

  return {
    dialog,
    form,
    save,
    create,
    edit,
    isEditing,
  }
}
