import Alpine, { AlpineComponent } from 'alpinejs'
import collapse from '@alpinejs/collapse'
import intersect from '@alpinejs/intersect'
import persist from '@alpinejs/persist'

// start: Component Library Imports //
import LogoBar, { LogoBarComponent } from "../Fusion/Presentation/Components/LogoBar/LogoBar";
// end: Component Library Imports //

// We decided to use https://alpinejs.dev/ to write js code
// as it provides a great way to structure and develop js components.
Alpine.plugin(collapse)
Alpine.plugin(intersect)
Alpine.plugin(persist)

// Components
// start: Component Library Components //
Alpine.data('logoBar', LogoBar as (value: any) => AlpineComponent<LogoBarComponent>)
// end: Component Library Components //

// Assign a custom prefix:
Alpine.prefix("components-");
Alpine.start()
