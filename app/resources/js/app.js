import './bootstrap';
import '../css/root.css';
import.meta.glob('../views/**/*.css', { eager: true });

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import focus from '@alpinejs/focus'
import shareLink from './alpinejs/shareLink.js'
import guthabenChart from './alpinejs/guthabenChart.js'
import investmentChart from './alpinejs/investmentChart.js'

Alpine.plugin(focus)

Alpine.data('shareLink', shareLink)
Alpine.data('guthabenChart', guthabenChart)
Alpine.data('investmentChart', investmentChart)
Livewire.start()
