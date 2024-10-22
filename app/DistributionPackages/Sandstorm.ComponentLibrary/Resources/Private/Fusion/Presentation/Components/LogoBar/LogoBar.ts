import {AlpineComponent} from 'alpinejs'
import Swiper from 'swiper/bundle'
import basicSlider, {AbstractSliderComponent, CSS_CLASSES} from '../Slider/AbstractSlider'

export type LogoBarComponent = AbstractSliderComponent & {
    amountOfLogos: number
    autoplayInterval: number
}

const LogoBar = (
    amountOfLogos: number = 6,
    autoplayInterval: number = 3000,
    inBackend: boolean = false
): AlpineComponent<LogoBarComponent> => {
    return {
        amountOfLogos: amountOfLogos,
        autoplayInterval: autoplayInterval,

        // We are not rendering the arrows here, so we don't need the a11y messages for prev and next slide
        ...basicSlider('', '', inBackend),

        _initSlider() {
            const swiperRef = this.$refs.slider
            const amountOfSlides = swiperRef.querySelectorAll(CSS_CLASSES.slide).length

            if (amountOfSlides === 0) {
                return
            }

            const swiperOptions = {
                loop: !this.inBackend,
                slidesPerView: 2, // for mobile
                spaceBetween: 20,
                pauseOnMouseEnter: false,
                autoplay: this.inBackend
                    ? false
                    : {
                        disableOnInteraction: false,
                        delay: this.autoplayInterval && !this.inBackend ? this.autoplayInterval : undefined
                    },
                breakpoints: {
                    // when window width is >= 996px
                    996: {
                        slidesPerView: this.amountOfLogos,
                        spaceBetween: 40,
                    },
                    // when window width is >= 768px
                    768: {
                        slidesPerView: 4,
                    },
                },
            }

            this._swiper = new Swiper(swiperRef, swiperOptions)
        },
    }
}

export default LogoBar
