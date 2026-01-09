import { TatraBanka } from '@/Components/Logo'
import type { Component } from 'vue'

export interface BankAccountType {
  id: string
  name: string
  description: string
  logo: Component
}

export const BankAccountTypes: Array<BankAccountType> = [
  {
    id: 'tatra-banka-bmail',
    name: 'TatraBanka B-Mail',
    description: 'Pripojte si Váš účet v Tatra Banke prostredníctvom služby B-Mail.',
    logo: TatraBanka,
  }
]
