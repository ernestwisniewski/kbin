import GLightbox from 'glightbox';

export default class KChoices {
    constructor() {
        const params = {selector: '.kbin-thumb', openEffect: 'none', closeEffect: 'none', slideEffect: 'none'};
        const glightbox = GLightbox(params);

        document.addEventListener('turbo:load', (event) => {
            const glightbox = GLightbox(params);
        });
    }
}
