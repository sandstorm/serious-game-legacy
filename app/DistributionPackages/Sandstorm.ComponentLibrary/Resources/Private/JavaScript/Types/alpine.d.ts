export * from 'alpinejs'

declare module 'alpinejs' {
    interface Alpine {
        /**
         * Retrieves state in the global store and casts it to the specific store type.
         *
         * @param name state key
         */
        store<T>(name: string): T
        /**
         * Retrieves state in the global store and casts it to the specific store type.
         *
         * @param name state key
         * @param value the initial state value
         */
        store<T>(name: string, value: T): T
        $persist: (value: any) => any
    }
}
