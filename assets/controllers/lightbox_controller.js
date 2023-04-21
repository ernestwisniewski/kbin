import {Controller} from '@hotwired/stimulus';
import GLightbox from 'glightbox';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    connect() {
        const params = {selector: '.thumb', openEffect: 'none', closeEffect: 'none', slideEffect: 'none'};
        GLightbox(params);
    }
}