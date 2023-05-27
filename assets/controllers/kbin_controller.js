import {ApplicationController, useDebounce} from 'stimulus-use'
import router from "../utils/routing";
import {fetch, ok} from "../utils/http";

/* stimulusFetch: 'lazy' */
export default class extends ApplicationController {
    static values = {
        loading: Boolean,
    }

    static debounces = ['mention']

    connect() {
        useDebounce(this, {wait: 800})
        this.handleDropdowns();
        this.handleOptionsBarScroll();
        this.handleDefaultTheme();
    }

    handleDropdowns() {
        this.element.querySelectorAll('.dropdown > a').forEach((dropdown) => {
            dropdown.addEventListener('click', (event) => {
                event.preventDefault();
            });
        });
    }

    async mention(event) {
        if (false === event.target.matches(':hover')) {
            return;
        }

        try {
            let param = event.params.username;

            if (param.charAt(0) === "@") {
                param = param.substring(1);
            }
            const username = param.includes('@') ? `@${param}` : param;
            const url = router().generate('ajax_fetch_user_popup', {username: username});

            this.loadingValue = true;

            let response = await fetch(url);

            response = await ok(response);
            response = await response.json();

            document.querySelector('.popover').innerHTML = response.html;

            popover.trigger = event.target;
            popover.selectedTrigger = event.target;
            popover.element.dispatchEvent(new Event('openPopover'));
        } catch (e) {
        } finally {
            this.loadingValue = false;
        }
    }

    handleOptionsBarScroll() {
        // const containers = document.querySelectorAll('.options__main');
        // containers.forEach((container) => {
        //     container.addEventListener("wheel", (event) => {
        //         event.preventDefault();
        //         container.scrollLeft += event.deltaY;
        //     });
        // });

        const container = document.getElementById('options');
        const containerWidth = container.clientWidth;

        const area = container.querySelector('.options__main');
        const areaWidth = area.scrollWidth;

        if (areaWidth > containerWidth) {
            container.insertAdjacentHTML('beforeend', '<menu class="scroll"><li class="scroll-left me-1"><i class="fa-solid fa-circle-left"></i></li><li class="scroll-right"><i class="fa-solid fa-circle-right"></i></li></menu>');

            const scrollLeft = container.querySelector('.scroll-left');
            const scrollRight = container.querySelector('.scroll-right');
            const scrollBtnContainer = container.querySelector('.scroll');
            const scrollArea = container.querySelector('.options__main');

            container.style.position = 'relative';
            scrollBtnContainer.style.position = 'absolute';
            scrollBtnContainer.style.right = '0';
            scrollBtnContainer.style.bottom = '-10px';

            scrollLeft.style.cursor = 'pointer';
            scrollRight.style.cursor = 'pointer';

            scrollRight.addEventListener('click', () => {
                scrollArea.scrollLeft += 100;
            });
            scrollLeft.addEventListener('click', () => {
                scrollArea.scrollLeft -= 100;
            });
        }
    }

    handleDefaultTheme() {
        if (!document.querySelector('body').classList.contains('theme--default')) {
            return;
        }

        let preferredTheme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';

        const now = new Date();
        const expireTime = now.getTime() + 60 * 60 * 1000;
        const expireDate = new Date(expireTime).toUTCString();
        document.cookie = `kbin_theme=${preferredTheme}; expires=${expireDate}; path=/`;

        document.querySelector('body').classList.remove('theme--default');
        document.querySelector('body').classList.add(`theme--${preferredTheme}`);
    }

    openNav(e) {
        e.preventDefault();
        if (window.screen.width >= 992) {
            window.location = e.target.closest('a').href;
            return;
        }

        const sidebar = document.getElementById('sidebar');
        if (sidebar.classList.contains('open')) {
            window.location = e.target.closest('a').href;
        }

        sidebar.classList.add('open');
    }

    closeNav(e) {
        document.getElementById('sidebar').classList.remove('open');
    }
}
