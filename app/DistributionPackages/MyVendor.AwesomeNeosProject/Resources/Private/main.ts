import Alpine, { AlpineComponent } from 'alpinejs'
import collapse from '@alpinejs/collapse'
import intersect from '@alpinejs/intersect'
import persist from '@alpinejs/persist'
import './main.scss'
import { initMap } from './Fusion/Presentation/Components/Map/Map'
import slider from './Fusion/Presentation/Components/Slider/Slider'
import logowall from './Fusion/Presentation/Components/Logowall/Logowall'
import ButtonToTop, { ButtonToTopComponent } from './Fusion/Presentation/Components/ButtonToTop/ButtonToTop'
import EventList, { EventListComponent } from './Fusion/Presentation/Components/Event/List/EventList'

// We decided to use https://alpinejs.dev/ to write js code
// as it provides a great way to structure and develop js components.
Alpine.plugin(collapse)
Alpine.plugin(intersect)
Alpine.plugin(persist)

initMap(Alpine)

Alpine.data('logowall', logowall)
Alpine.data('slider', slider)
Alpine.data('buttonToTop', ButtonToTop as (value: any) => AlpineComponent<ButtonToTopComponent>)
Alpine.data('eventList', EventList as (value: any) => AlpineComponent<EventListComponent>)

Alpine.start()
