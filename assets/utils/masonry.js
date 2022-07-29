import Masonry from 'masonry-layout';
import imagesLoaded from 'imagesloaded'

export default class KMasonry {
    constructor() {
        document.querySelectorAll('.kbin-masonry').forEach(el => {
            this.build(el);
        });

        document.addEventListener('turbo:load', (event) => {

            location.reload(); // @todo masonry bug in turbo mode
            // event.target.querySelectorAll('.kbin-masonry').forEach(el => {
            //     this.build(el);
            // });
        });
    }

    build(el) {
        let grid = new Masonry(el, {
            itemSelector: '.kbin-masonry-item',
        });

        let imgLoad = imagesLoaded('#kbin');

        imgLoad.on( 'progress', function( instance, image ) {
            grid.reloadItems();
        });
    }
}
