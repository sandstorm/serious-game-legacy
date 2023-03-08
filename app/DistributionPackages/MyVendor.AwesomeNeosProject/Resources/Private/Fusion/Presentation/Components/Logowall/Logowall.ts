// @ts-ignore
import Swiper from 'swiper/bundle';
// import Swiper styles
import 'swiper/css/bundle';
// @ts-ignore
import { Swiper as SwiperType } from "swiper";

export default (amountOfLogos: unknown = 6, autoplayInterval: unknown = 3000, inBackend: unknown = false) => ({
    amountOfLogos: amountOfLogos as unknown,
    autoplayInterval: autoplayInterval as number,
    inBackend: inBackend as boolean,

    swiper: null as SwiperType | null,

    // init is called before alpine.js renders the appropriate component in the DOM.
    // In this case we create a new Swiper for the referenced Logowall (x-ref="logowallSlider") in the DOM.
    init() {
        this._initSlider();
    },

    _initSlider() {
        // @ts-ignore
        const swiperRef = this.$refs.logowallSlider;
        const amountOfSlides = swiperRef.querySelectorAll('.swiper-slide').length;
        if (amountOfSlides === 0) {
            return;
        }

        let autoplay = undefined;
        if (this.autoplayInterval && !this.inBackend) {
            autoplay = {
                delay: this.autoplayInterval,
            }
        }

        const swiperOptions = {
            loop: !this.inBackend,
            slidesPerView: 2, // for mobile
            spaceBetween: 20,
            pauseOnMouseEnter: false,
            autoplay: autoplay,
            breakpoints: {
                // when window width is >= 996px
                996: {
                    slidesPerView: this.amountOfLogos,
                    spaceBetween: 40
                },
                // when window width is >= 768px
                768: {
                    slidesPerView: 4,
                },
            }
        }

        this.swiper = new Swiper(swiperRef, swiperOptions);
    }
})
