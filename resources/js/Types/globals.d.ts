import type { BackendToast } from "@/Components/Sonner/useBackendSonner.ts";
import { AppPageProps } from '@/Types';

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
  interface ImportMetaEnv {
    readonly VITE_APP_NAME: string;

    [key: string]: string | boolean | undefined;
  }

  interface ImportMeta {
    readonly env: ImportMetaEnv;
    readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
  }
}

declare module '@inertiajs/core' {
  export interface InertiaConfig {
    flashDataType: {
      toast?: {
        title: string
        content: string | null
        variant: string
      }
      completeBankMailIntegration?: {
        email: string
        helpLink: string | null
      }
      recentlyCreatedApiKey?: {
        token: string
      }
    }
  }

  interface PageProps extends InertiaPageProps, AppPageProps {
    locales: Array<{
      name: string
      code: string
      required: boolean
    }>
    locale: string
    fallbackLocale: string
  }
}

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $inertia: typeof Router;
    $page: Page;
    $headManager: ReturnType<typeof createHeadManager>;
  }
}
