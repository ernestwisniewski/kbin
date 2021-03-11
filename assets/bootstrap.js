import {startStimulusApp} from '@symfony/stimulus-bridge';
import '@symfony/autoimport';
import Reveal from 'stimulus-reveal-controller'
import Clipboard from 'stimulus-clipboard'
import KChoices from './controllers/utils/choices';
import KDatepicker from './controllers/utils/datepicker';
import KEditor from './controllers/utils/editor';
import KLoginAlert from './controllers/utils/login-alert';
import ScrollProgress from "stimulus-scroll-progress"


// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context('./controllers', true, /\.(j|t)sx?$/));

app.register('reveal', Reveal);
app.register('clipboard', Clipboard);
app.register("scroll-progress", ScrollProgress)

window.addEventListener('load', function(event) {
    const choices = new KChoices();
    const datepicker = new KDatepicker();
    const editor = new KEditor();
    const loginAlert = new KLoginAlert();
});
