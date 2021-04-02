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

const url = new URL('https://localhost/.well-known/mercure');
url.searchParams.append('topic', '/api/magazines/karabin');
const eventSource = new EventSource(url);
eventSource.onmessage = e => console.log(e); // do something with the payload
