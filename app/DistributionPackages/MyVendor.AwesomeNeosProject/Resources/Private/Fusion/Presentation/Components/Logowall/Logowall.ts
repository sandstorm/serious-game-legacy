// @ts-ignore
import Swiper from 'swiper/bundle';
// import Swiper styles
import 'swiper/css/bundle';
// @ts-ignore
import { Swiper as SwiperType } from "swiper";
import basicSlider from "../Slider/Slider";

export default function Logowall(amountOfLogos: unknown = 6, autoplayInterval: unknown = 3000, inBackend: unknown = false) {
    return {
        // Function signature: function(prevSlideMessage: string, nextSlideMessage: string, inBackend: unknown = false)
        // We are not rendering the arrows here, so we don't need the a11y messages for prev and next slide
        ...basicSlider('', '', inBackend), 

        amountOfLogos: amountOfLogos as number,
        autoplayInterval: autoplayInterval as number,

        _initSlider() {
            // @ts-ignore
            const swiperRef = this.$refs.slider;
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
                autoplay: this.inBackend ? false : {
                    disableOnInteraction: false,
                },
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

            this._swiper = new Swiper(swiperRef, swiperOptions);
        }
    }
}
