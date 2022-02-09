export default class Navbar {
    constructor() {
        // https://bootstrap-menu.com/detail-autohide.html
        this.build();

        document.addEventListener('turbo:load', (event) => {
            this.build()
        });
    }

    build() {
        const el_autohide = document.querySelector('.autohide');

        if (el_autohide) {
            var last_scroll_top = 0;
            window.addEventListener('scroll', function () {
                let scroll_top = window.scrollY;

                if (last_scroll_top < 20 || last_scroll_top < 20) {
                    el_autohide.classList.remove('scrolled-down');
                    el_autohide.classList.add('scrolled-up');
                    setTimeout(() => {
                        el_autohide.classList.remove('scrolled-up');
                    }, 500);
                } else if (scroll_top < last_scroll_top) {
                    el_autohide.classList.remove('scrolled-down');
                    el_autohide.classList.add('scrolled-up');
                    setTimeout(() => {
                        el_autohide.classList.remove('scrolled-up');
                    }, 500);
                } else {
                    el_autohide.classList.remove('scrolled-up');
                    el_autohide.classList.add('scrolled-down');
                }

                last_scroll_top = scroll_top;
            });
        }
    }
}
