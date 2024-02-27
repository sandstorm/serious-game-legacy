import { AlpineComponent } from "alpinejs"
import { beforeEach, describe, expect, it, vi } from 'vitest'
import EventList, { EventListComponent } from "./EventList"

describe('EventList', () => {
    let instance: AlpineComponent<EventListComponent>

    beforeEach(() => {
        instance = EventList()
    })

    it('should toggle filter', () => {
        // Arrange
        instance.filterOpen = false

        // Act
        instance.toggleFilter()

        // Assert
        expect(instance.filterOpen).toBeTruthy()
    })

    it('should close filter', () => {
        // Arrange
        instance.filterOpen = true

        // Act
        instance.closeFilter()

        // Assert
        expect(instance.filterOpen).toBeFalsy()
    })
})
