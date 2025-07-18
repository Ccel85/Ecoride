

import './bootstrap.js';
//import 'bootstrap/dist/js/bootstrap.min.js';

console.log('JS chargé');
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM chargé');
});
// Import Bootstrap
import 'bootstrap';

//CSS Bootstrap + icônes + CSS perso
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap-icons/font/bootstrap-icons.min.css';
import './styles/app.css';

import './js/toolTips.js';
import './js/allComments.js';

// Vérification que le fichier est chargé
console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
