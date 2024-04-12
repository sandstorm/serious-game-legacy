import Alpine, { AlpineComponent } from 'alpinejs'
import collapse from '@alpinejs/collapse'
import intersect from '@alpinejs/intersect'
import persist from '@alpinejs/persist'
import ButtonToTop, { ButtonToTopComponent } from '../Fusion/Presentation/Components/ButtonToTop/ButtonToTop'
import EventList, { EventListComponent } from '../Fusion/Presentation/Components/Event/List/EventList'
import Map, { MapComponent } from '../Fusion/Presentation/Components/Map/Map'
import Slider, { SliderComponent } from '../Fusion/Presentation/Components/Slider/Slider'

// start: Component Library Imports //
// end: Component Library Imports //

// We decided to use https://alpinejs.dev/ to write js code
// as it provides a great way to structure and develop js components.
Alpine.plugin(collapse)
Alpine.plugin(intersect)
Alpine.plugin(persist)

// Components
Alpine.data('buttonToTop', ButtonToTop as (value: any) => AlpineComponent<ButtonToTopComponent>)
Alpine.data('eventList', EventList as (value: any) => AlpineComponent<EventListComponent>)
Alpine.data('map', Map as (value: any) => AlpineComponent<MapComponent>)
Alpine.data('slider', Slider as (value: any) => AlpineComponent<SliderComponent>)

// start: Component Library Components //
// end: Component Library Components //

// start: replace with Alpine.start() on kickstart //
// We write Alpine to global window object to make it available in the component library main.ts.
// Aline is initialized in the component library main.ts to avoid two initializations.
// @ts-ignore
if( !window.Alpine ) window.Alpine = Alpine;
// end: replace wirth Alpine.start() on kickstart //

