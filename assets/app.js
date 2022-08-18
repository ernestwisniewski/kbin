import './bootstrap'
import './styles/app.scss';
import KChoices from "./utils/choices";
import KDatepicker from "./utils/datepicker";
import KEditor from "./utils/editor";
import Navbar from "./utils/navbar";
import KMasonry from './utils/masonry';
import KLightbox from './utils/lightbox';

window.addEventListener('load', function (event) {
    const choices = new KChoices();
    const datepicker = new KDatepicker();
    const editor = new KEditor();
    const navbar = new Navbar();
    const masonry = new KMasonry();
    const lightbox = new KLightbox();
});
