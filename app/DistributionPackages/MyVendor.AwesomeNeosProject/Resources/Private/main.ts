// @ts-ignore
import Alpine from 'alpinejs';
// @ts-ignore
import collapse from '@alpinejs/collapse';
// @ts-ignore
import intersect from '@alpinejs/intersect';
import "./main.scss";
import eventList from "./Fusion/Presentation/Components/Event/List/EventList";
import {initMap} from "./Fusion/Presentation/Components/Map/Map";
import slider from "./Fusion/Presentation/Components/Slider/Slider";
import logowall from "./Fusion/Presentation/Components/Logowall/Logowall";
import toTopButton from "./Fusion/Presentation/Components/ToTopButton/ToTopButton";

// We decided to use https://alpinejs.dev/ to write js code
// as it provides a great way to structure and develop js components.
Alpine.plugin(collapse);
Alpine.plugin(intersect);
initMap(Alpine);

Alpine.data('eventList', eventList);
Alpine.data('logowall', logowall);
Alpine.data('slider', slider);
Alpine.data('toTopButton', toTopButton);

Alpine.start();
