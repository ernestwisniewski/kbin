import {startStimulusApp} from '@symfony/stimulus-bridge';
import '@symfony/autoimport';
import KChoices from "./controllers/utils/choices";
import KDatepicker from "./controllers/utils/datepicker";

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context('./controllers', true, /\.(j|t)sx?$/));

window.addEventListener('load', function(event) {
    const choices = new KChoices();
    const datepicker = new KDatepicker();
});
