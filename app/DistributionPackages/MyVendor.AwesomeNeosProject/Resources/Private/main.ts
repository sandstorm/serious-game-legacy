// @ts-ignore
import Alpine from 'alpinejs';
// @ts-ignore
import collapse from '@alpinejs/collapse';
// @ts-ignore
import intersect from '@alpinejs/intersect';
import "./main.scss";
import {initMap} from "./Fusion/Presentation/Components/Map/Map";
import slider from "./Fusion/Presentation/Components/Slider/Slider";
import logowall from "./Fusion/Presentation/Components/Logowall/Logowall";

// We decided to use https://alpinejs.dev/ to write js code
// as it provides a great way to structure and develop js components.
Alpine.plugin(collapse);
Alpine.plugin(intersect);
initMap(Alpine);

Alpine.data('slider', slider);
Alpine.data('logowall', logowall);

Alpine.start();
