import { AlpineComponent } from "alpinejs"
import { beforeEach, describe, expect, it } from 'vitest'
import ButtonToTop, { ButtonToTopComponent } from "./ButtonToTop"

describe('ButtonToTop', () => {
    let instance: AlpineComponent<ButtonToTopComponent>

    beforeEach(() => {
        // Create a mock Alpine.js instance
        const el = document.createElement('button')
        el.innerHTML = '<button x-data="buttonToTop" x-ref="buttonToTop">To top</button>'
        document.body.appendChild(el)

        instance = ButtonToTop()

        // @ts-ignore
        instance.$refs = {
            buttonToTop: el
        }

        instance.init()
    })

    it('should get scroll direction down', () => {
        // Arrange
        instance.previousScrollPosition = 10
        window.scrollY = 100

        // Act
        const isScrollingDown = instance.isScrollingDown()

        // Assert
        expect(isScrollingDown).toBeTruthy()
    })

    it('should get scroll direction up', () => {
        // Arrange
        instance.previousScrollPosition = 100
        window.scrollY = 10

        // Act
        const isScrollingDown = instance.isScrollingDown()

        // Assert
        expect(isScrollingDown).toBeFalsy()
    })
})
