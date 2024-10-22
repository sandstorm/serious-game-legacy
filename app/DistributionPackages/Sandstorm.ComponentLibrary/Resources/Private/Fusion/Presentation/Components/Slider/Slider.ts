import { AlpineComponent } from 'alpinejs'
import Swiper from 'swiper/bundle'
import basicSlider, {AbstractSliderComponent, CSS_CLASSES} from './AbstractSlider'

export type SliderComponent = AbstractSliderComponent

export default (
    prevSlideMessage: string,
    nextSlideMessage: string,
    inBackend: boolean = false
): AlpineComponent<SliderComponent> => {
    return {
        // We are not rendering the arrows here, so we don't need the a11y messages for prev and next slide
        ...basicSlider(prevSlideMessage, nextSlideMessage, inBackend),

        _initSlider() {
            const swiperRef = this.$refs.slider
            const amountOfSlides = swiperRef.querySelectorAll(CSS_CLASSES.slide).length

            if (amountOfSlides === 0) {
                return
            }

            const swiperOptions = {
                loop: !this.inBackend,
                pagination: {
                    el: CSS_CLASSES.pagination,
                    clickable: true,
                },
                navigation: {
                    nextEl: CSS_CLASSES.nextEl,
                    prevEl: CSS_CLASSES.prevEl,
                },
                a11y: {
                    prevSlideMessage: this.prevSlideMessage,
                    nextSlideMessage: this.nextSlideMessage,
                },
            }

            this._swiper = new Swiper(swiperRef, swiperOptions)
        },
    }
}
