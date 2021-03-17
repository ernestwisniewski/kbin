import {startStimulusApp} from '@symfony/stimulus-bridge';
import Reveal from 'stimulus-reveal-controller'
import Clipboard from 'stimulus-clipboard'
import ScrollProgress from "stimulus-scroll-progress"
import chartj from "stimulus-scroll-progress"


// Registers Stimulus controllers from controllers.json and in the controllers/ directory
export const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.(j|t)sx?$/
));

app.register('reveal', Reveal);
app.register('clipboard', Clipboard);
app.register("scroll-progress", ScrollProgress)

