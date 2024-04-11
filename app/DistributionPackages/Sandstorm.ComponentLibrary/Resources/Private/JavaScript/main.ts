import LogoBar, { LogoBarComponent } from "../Fusion/Presentation/Components/LogoBar/LogoBar";
import {AlpineComponent} from "alpinejs";

// @ts-ignore
window.Alpine.data('logoBar', LogoBar as (value: any) => AlpineComponent<LogoBarComponent>)
// @ts-ignore
window.Alpine.start()
