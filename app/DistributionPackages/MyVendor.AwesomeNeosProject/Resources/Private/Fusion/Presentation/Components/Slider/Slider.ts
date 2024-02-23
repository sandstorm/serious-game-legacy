import { AlpineComponent } from 'alpinejs'
import Swiper from 'swiper/bundle'
import { SwiperOptions, Swiper as SwiperType } from 'swiper'
// import Swiper styles
import 'swiper/css/bundle'

const CSS_CLASSES = {
    // use the generic swiper classes here so the code works for all swipers inheriting this function
    slider: '.swiper',
    slide: 'swiper-slide',
    pagination: '.swiper-pagination',
    // We need additional specific classnames for the navigation arrows to make sliders work separatly
    // Otherwise all slider are connect, which means when I navigation in one slider another slider on the same
    // page will also navigate to the next slide
    nextEl: '.swiper-button-next.swiper-button-next--basic-slider',
    prevEl: '.swiper-button-prev.swiper-button-prev--basic-slider',
}

export type SliderComponent = {
    inBackend: boolean
    prevSlideMessage: string
    nextSlideMessage: string

    _currentPosition: number
    _swiper: SwiperType | null

    init: () => void
    getSlideIndex: (element: HTMLElement) => number
    isSlide: (element: HTMLElement) => boolean
    isOwnSlide: (element: HTMLElement) => boolean
    nodeSelected: (event: any) => void
    nodeRemoved: (event: any) => void
    nodeCreated: (event: any) => void
    _initSlider: () => void
}

export default (
    prevSlideMessage: string,
    nextSlideMessage: string,
    inBackend: boolean = false
): AlpineComponent<SliderComponent> => {
    return {
        inBackend: inBackend,
        prevSlideMessage: prevSlideMessage,
        nextSlideMessage: nextSlideMessage,

        _currentPosition: 1,
        _swiper: null as SwiperType | null,

        // init is called before alpine.js renders the appropriate component in the DOM.
        // In this case we create a new Swiper for the referenced Slider (x-ref="slider") in the DOM.
        init() {
            this._initSlider()

            if (this.inBackend) {
                const scope = this
                // listen to node events to scroll to the right slide in neos backend
                document.addEventListener(
                    'Neos.NodeCreated',
                    function (event) {
                        scope.nodeCreated(event)
                    },
                    false
                )
                document.addEventListener(
                    'Neos.NodeSelected',
                    function (event) {
                        scope.nodeSelected(event)
                    },
                    false
                )
                document.addEventListener(
                    'Neos.NodeRemoved',
                    function (event) {
                        scope.nodeRemoved(event)
                    },
                    false
                )
            }
        },

        // Backend Optimizations
        getSlideIndex(element) {
            const slides = Array.from(this.$refs.slider.querySelectorAll(`.${CSS_CLASSES.slide}`))
            return slides.indexOf(element)
        },

        isSlide(element) {
            return element.classList.contains(CSS_CLASSES.slide)
        },

        isOwnSlide(element) {
            if (!this.isSlide(element)) return false
            return element.closest(CSS_CLASSES.slider) === this.$refs.slider
        },

        nodeSelected(event) {
            if (this._swiper && this.isOwnSlide(event.detail.element)) {
                const index = this.getSlideIndex(event.detail.element)
                if (index >= 0) {
                    this._swiper.update()
                    this._swiper.slideTo(index)
                }
            }
        },

        nodeRemoved(event) {
            if (this._swiper && this.isSlide(event.detail.element)) {
                // we update all sliders in the page as the parent is null
                // and we cannot check if it is our own slide
                this._swiper.update()
            }
        },

        nodeCreated(event) {
            if (this._swiper && this.isOwnSlide(event.detail.element)) {
                this._swiper.update()
            }
        },

        _initSlider() {
            const swiperRef = this.$refs.slider

            const amountOfSlides = swiperRef.querySelectorAll(`.${CSS_CLASSES.slide}`).length

            if (amountOfSlides === 0) {
                return
            }

            const swiperOptions: SwiperOptions = {
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
