import { defineConfig } from 'vitest/config'

export default defineConfig({
    test: {
        include: ['./**/*.test.ts'],
        environment: "jsdom",
        coverage: {
            reportsDirectory: './JavaScript/Tests/Coverage'
        }
    },
})
