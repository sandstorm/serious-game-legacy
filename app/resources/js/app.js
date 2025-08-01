import './bootstrap';

import '../css/root.css';
import.meta.glob('../views/**/*.css', { eager: true });

import Alpine from 'alpinejs'
import focus from '@alpinejs/focus'

Alpine.plugin(focus)
