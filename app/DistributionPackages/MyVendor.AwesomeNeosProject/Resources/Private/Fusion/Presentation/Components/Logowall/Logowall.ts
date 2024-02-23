import { AlpineComponent } from 'alpinejs'
import Swiper from 'swiper/bundle'
import { SwiperOptions, Swiper as SwiperType } from 'swiper'
// import Swiper styles
import 'swiper/css/bundle'
import basicSlider, { SliderComponent } from '../Slider/Slider'

const CSS_CLASSES = {
    // use the generic swiper classes here so the code works for all swipers inheriting this function
    slide: '.swiper-slide',
}

export type LogowallComponent = SliderComponent & {
    amountOfLogos: number
    autoplayInterval: number
}

export default (
    amountOfLogos: number = 6,
    autoplayInterval: number = 3000,
    inBackend: boolean = false
): AlpineComponent<LogowallComponent> => {
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

            const swiperOptions: SwiperOptions & { pauseOnMouseEnter: boolean } = {
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
