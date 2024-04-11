import Alpine, { AlpineComponent } from 'alpinejs'
import collapse from '@alpinejs/collapse'
import intersect from '@alpinejs/intersect'
import persist from '@alpinejs/persist'
import { ExampleStore } from './Types/types'
import { EXAMPLE_STORE } from './store'
import ButtonToTop, { ButtonToTopComponent } from '../Fusion/Presentation/Components/ButtonToTop/ButtonToTop'
import EventList, { EventListComponent } from '../Fusion/Presentation/Components/Event/List/EventList'
import Map, { MapComponent } from '../Fusion/Presentation/Components/Map/Map'
import Slider, { SliderComponent } from '../Fusion/Presentation/Components/Slider/Slider'

// start: Component Library Imports //
import LogoBar, { LogoBarComponent } from '../../../../Sandstorm.ComponentLibrary/Resources/Private/Fusion/Presentation/Components/LogoBar/LogoBar'
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
Alpine.data('logoBar', LogoBar as (value: any) => AlpineComponent<LogoBarComponent>)
// end: Component Library Components //

// Stores
Alpine.store<ExampleStore>(EXAMPLE_STORE, {
    frontendVersion: Alpine.$persist('vx.x.x').as('frontend_version')
})

Alpine.start()
