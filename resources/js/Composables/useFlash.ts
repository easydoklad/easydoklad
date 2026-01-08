import type { FlashData } from '@inertiajs/core'
import { router } from '@inertiajs/vue3'
import { onBeforeUnmount, onMounted } from 'vue'

export function useFlash<T extends keyof FlashData>(key: T, callback: (value: NonNullable<FlashData[T]>) => void) {
  let cancel: VoidFunction | undefined = undefined

  onMounted(() => {
    cancel = router.on('flash', event => {
      const value = event.detail.flash[key]

      if (value) {
        callback(value)
      }
    })
  })

  onBeforeUnmount(() => {
    if (cancel) {
      cancel()
      cancel = undefined
    }
  })
}
