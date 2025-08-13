import './bootstrap';

import '../css/root.css';
import.meta.glob('../views/**/*.css', { eager: true });

import Alpine from 'alpinejs'
import focus from '@alpinejs/focus'
import playerList from './alpinejs/playerList'

Alpine.plugin(focus)

Alpine.data('playerList', playerList)
Alpine.start()
