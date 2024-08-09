// import { createApp } from 'vue';
// import App from './Vue/App.vue';
// import './styles/app.css'; // Importuj Tailwind CSS
//
// createApp(App).mount('#app');

// assets/app.js
import 'bootstrap';
// CSS
import './styles/app.scss';
import './styles/leftBar.css';


//JS
import './js/test.js';
import {testJs} from "./js/test.js";
import axios from "axios";


window.testJs = testJs;
window.axios = axios;