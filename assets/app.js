import './bootstrap.js';
import './bootstrap.bundle.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/vendor/boxicons.min.css'; // Boxicons
import './styles/vendor/bootstrap.min.css'; // Bootstrap

import './build/app.css';

// production deploy > php bin/console asset-map:compile