import './bootstrap'
import './styles/app.scss';
import KChoices from "./utils/choices";
import KDatepicker from "./utils/datepicker";
import KEditor from "./utils/editor";
import KLoginAlert from "./utils/login-alert";
import Navbar from "./utils/navbar";
import bootstrap from 'bootstrap/dist/js/bootstrap.bundle.js';

window.bootstrap = bootstrap;

window.addEventListener('load', function (event) {
    const choices = new KChoices();
    const datepicker = new KDatepicker();
    const editor = new KEditor();
    const loginAlert = new KLoginAlert();
    const navbar = new Navbar();
});

