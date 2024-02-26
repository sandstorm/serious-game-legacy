import Alpine from 'alpinejs'
import { ExampleStore } from './Types/types'

export const EXAMPLE_STORE = 'example'
// In sync with store
export const getExampleStore = (): ExampleStore => Alpine.store<ExampleStore>(EXAMPLE_STORE)
// Extract proxy data but not in sync with store (store will not be updated when changing this data)
export const getRawExampleStore = (): ExampleStore => Alpine.raw(getExampleStore())
