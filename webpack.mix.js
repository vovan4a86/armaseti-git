let mix = require('laravel-mix');

mix.disableNotifications();

mix.js('resources/assets/js--sources/main.js', 'public/static/js/all.js')
    .css('resources/assets/css/main.css', 'public/static/css/all.css');
