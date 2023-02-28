// @ts-ignore
import Swiper from 'swiper/bundle';
// import Swiper styles
import 'swiper/css/bundle';
import {Swiper as SwiperType} from "swiper";

const SLIDE_CLASS = "content-slider__slide";
const SLIDER_CLASS = "content-slider";

export default (inBackend: unknown = false) => ({
    inBackend: inBackend as boolean,
    _currentPosition: 1,
    swiper: null as SwiperType | null,

    // init is called before alpine.js renders the appropriate component in the DOM.
    // In this case we create a new Swiper for the referenced Slider (x-ref="slider") in the DOM.
    init() {
        const scope = this;

        this._initSlider();
        if (this.inBackend) {
            // listen to node events to scroll to the right slide in neos backend
            document.addEventListener('Neos.NodeCreated', function (event) {
                scope.nodeCreated(event)
            }, false);
            document.addEventListener('Neos.NodeSelected', function (event) {
                scope.nodeSelected(event)
            }, false);
            document.addEventListener('Neos.NodeRemoved', function (event) {
                scope.nodeRemoved(event)
            }, false);
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
        if (this.swiper && this.isOwnSlide(event.detail.element)) {
            const index = this.getSlideIndex(event.detail.element);
            if (index >= 0) {
                this.swiper.update();
                this.swiper.slideTo(index);
            }
        }
    },

    nodeRemoved(event: any) {
        if (this.swiper && this.isSlide(event.detail.element)) {
            // we update all sliders in the page as the parent is null
            // and we cannot check if it is our own slide
            this.swiper.update();
        }
    },

    nodeCreated(event: any) {
        if (this.swiper && this.isOwnSlide(event.detail.element)) {
            this.swiper.update();
        }
    },

    _initSlider() {
        // @ts-ignore
        const swiperRef = this.$refs.slider;

        // @ts-ignore
        const amountOfSlides = swiperRef.querySelectorAll('.swiper-slide').length;
        if (amountOfSlides === 0) {
            return;
        }

        const swiperOptions = {
            loop: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
        }

        // this would otherwise create copies of the slides breaking
        // the index calculation
        if (this.inBackend) swiperOptions.loop = false;

        this.swiper = new Swiper(swiperRef, swiperOptions);
    }
})
