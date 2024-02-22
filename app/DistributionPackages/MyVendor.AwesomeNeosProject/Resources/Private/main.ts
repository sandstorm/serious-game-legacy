import Alpine from 'alpinejs'
import collapse from '@alpinejs/collapse'
import intersect from '@alpinejs/intersect'
import persist from '@alpinejs/persist'
import './main.scss'
import eventList from './Fusion/Presentation/Components/Event/List/EventList'
import { initMap } from './Fusion/Presentation/Components/Map/Map'
import slider from './Fusion/Presentation/Components/Slider/Slider'
import logowall from './Fusion/Presentation/Components/Logowall/Logowall'
import buttonToTop from './Fusion/Presentation/Components/ButtonToTop/ButtonToTop'

// We decided to use https://alpinejs.dev/ to write js code
// as it provides a great way to structure and develop js components.
Alpine.plugin(collapse)
Alpine.plugin(intersect)
Alpine.plugin(persist)

initMap(Alpine)

Alpine.data('eventList', eventList)
Alpine.data('logowall', logowall)
Alpine.data('slider', slider)
Alpine.data('buttonToTop', buttonToTop)


Alpine.start()
