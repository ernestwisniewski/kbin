import './bootstrap'
import './styles/app.scss';
import "bootstrap/dist/js/bootstrap.min";
import KChoices from "./utils/choices";
import KDatepicker from "./utils/datepicker";
import KEditor from "./utils/editor";
import KLoginAlert from "./utils/login-alert";

window.addEventListener('load', function(event) {
    const choices = new KChoices();
    const datepicker = new KDatepicker();
    const editor = new KEditor();
    const loginAlert = new KLoginAlert();
});

