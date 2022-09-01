import {Controller} from '@hotwired/stimulus';
import GLightbox from 'glightbox';

export default class extends Controller {
    connect() {
        const params = {selector: '.kbin-thumb', openEffect: 'none', closeEffect: 'none', slideEffect: 'none'};
        GLightbox(params);
    }
}