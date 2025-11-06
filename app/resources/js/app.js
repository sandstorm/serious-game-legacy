import './bootstrap';
import '../css/root.css';
import.meta.glob('../views/**/*.css', { eager: true });

import { Livewire, Alpine } from '../../vendor/livewire/livewire/dist/livewire.esm';
import focus from '@alpinejs/focus'
import playerList from './alpinejs/playerList'
import shareLink from './alpinejs/shareLink.js'

Alpine.plugin(focus)

Alpine.data('playerList', playerList)
Alpine.data('shareLink', shareLink)
Livewire.start()
