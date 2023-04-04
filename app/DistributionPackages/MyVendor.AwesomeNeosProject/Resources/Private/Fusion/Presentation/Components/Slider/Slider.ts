// @ts-ignore
import Swiper from "swiper/bundle";
// import Swiper styles
import "swiper/css/bundle";
// @ts-ignore
import { Swiper as SwiperType } from "swiper";

// use the generic swiper classes here so the code works for all swipers inheriting this function
const SLIDE_CLASS = "swiper-slide";
const SLIDER_CLASS = "swiper";

export const basicSlider = function (prevSlideMessage: unknown, nextSlideMessage: unknown, inBackend: unknown = false) {
    return {
        inBackend: inBackend as boolean,
        prevSlideMessage: prevSlideMessage as string,
        nextSlideMessage: nextSlideMessage as string,

        _currentPosition: 1,
        _swiper: null as SwiperType | null,

        // init is called before alpine.js renders the appropriate component in the DOM.
        // In this case we create a new Swiper for the referenced Slider (x-ref="slider") in the DOM.
        init() {
            this._initSlider();
            if (this.inBackend) {
                const scope = this;
                // listen to node events to scroll to the right slide in neos backend
                document.addEventListener(
                    "Neos.NodeCreated",
                    function (event) {
                        scope.nodeCreated(event);
                    },
                    false
                );
                document.addEventListener(
                    "Neos.NodeSelected",
                    function (event) {
                        scope.nodeSelected(event);
                    },
                    false
                );
                document.addEventListener(
                    "Neos.NodeRemoved",
                    function (event) {
                        scope.nodeRemoved(event);
                    },
                    false
                );
            }
        },

        // Backend Optimizations
        getSlideIndex(element: HTMLElement): number {
            // @ts-ignore
            const slides = Array.from(this.$refs.slider.querySelectorAll(`.${SLIDE_CLASS}`));
            return slides.indexOf(element);
        },

        isSlide(element: HTMLElement): boolean {
            return element.classList.contains(SLIDE_CLASS);
        },

        isOwnSlide(element: HTMLElement): boolean {
            if (!this.isSlide(element)) return false;
            // @ts-ignore
            return element.closest(`.${SLIDER_CLASS}`) === this.$refs.slider;
        },

        nodeSelected(event: any) {
            if (this._swiper && this.isOwnSlide(event.detail.element)) {
                const index = this.getSlideIndex(event.detail.element);
                if (index >= 0) {
                    this._swiper.update();
                    this._swiper.slideTo(index);
                }
            }
        },

        nodeRemoved(event: any) {
            if (this._swiper && this.isSlide(event.detail.element)) {
                // we update all sliders in the page as the parent is null
                // and we cannot check if it is our own slide
                this._swiper.update();
            }
        },

        nodeCreated(event: any) {
            if (this._swiper && this.isOwnSlide(event.detail.element)) {
                this._swiper.update();
            }
        },

        _initSlider() {
            // @ts-ignore
            const swiperRef = this.$refs.slider;
            const amountOfSlides = swiperRef.querySelectorAll(`.${SLIDE_CLASS}`).length;
            if (amountOfSlides === 0) {
                return;
            }

            const swiperOptions = {
                loop: !this.inBackend,
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                a11y: {
                    prevSlideMessage: this.prevSlideMessage,
                    nextSlideMessage: this.nextSlideMessage,
                },
            };

            this._swiper = new Swiper(swiperRef, swiperOptions);
        },
    };
};

export default basicSlider;
