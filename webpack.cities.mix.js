let mix = require('laravel-mix');

mix.disableNotifications();
mix.setPublicPath('public/static/js');

mix.js('resources/assets/js--cities/cities.js', 'cities.js');
