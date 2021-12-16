import {startStimulusApp} from '@symfony/stimulus-bridge';
import Reveal from '@symfony/stimulus-bridge/lazy-controller-loader?lazy=true!stimulus-reveal-controller';
import Clipboard from '@symfony/stimulus-bridge/lazy-controller-loader?lazy=true!stimulus-clipboard';
import ScrollProgress from '@symfony/stimulus-bridge/lazy-controller-loader?lazy=true!stimulus-scroll-progress';
import Timeago from "./utils/timeago"

// Registers Stimulus controllers from controllers.json and in the controllers/ directory
const app = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.(j|t)sx?$/
));

app.register('reveal', Reveal);
app.register('clipboard', Clipboard);
app.register('scroll-progress', ScrollProgress);
app.register('timeago', Timeago);

export { app };
