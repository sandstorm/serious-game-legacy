import LogoBar, { LogoBarComponent } from "../Fusion/Presentation/Components/LogoBar/LogoBar";
import Map, { MapComponent } from '../Fusion/Presentation/Components/Map/Map'
import Slider, { SliderComponent } from '../Fusion/Presentation/Components/Slider/Slider'

import {AlpineComponent} from "alpinejs";

// @ts-ignore
window.Alpine.data('logoBar', LogoBar as (value: any) => AlpineComponent<LogoBarComponent>)
// @ts-ignore
window.Alpine.data('map', Map as (value: any) => AlpineComponent<MapComponent>)
// @ts-ignore
window.Alpine.data('slider', Slider as (value: any) => AlpineComponent<SliderComponent>)
// @ts-ignore
window.Alpine.start()
