import {ApplicationController, useDebounce} from 'stimulus-use'

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

    handleOptionsBarScroll() {

        const container = document.getElementById('options');
        const containerWidth = container.clientWidth;

        const area = container.querySelector('.options__main');
        const areaWidth = area.scrollWidth;

        if (areaWidth > containerWidth) {
            container.insertAdjacentHTML('beforeend', '<menu class="scroll"><li class="scroll-left"><i class="fa-solid fa-circle-left"></i></li><li class="scroll-right"><i class="fa-solid fa-circle-right"></i></li></menu>');

            const scrollLeft = container.querySelector('.scroll-left');
            const scrollRight = container.querySelector('.scroll-right');
            const scrollArea = container.querySelector('.options__main');

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

    /**
     * Handles interaction with the top nav bar menu items
     * @param {*} e 
     * @returns 
     */
    handleNavBarItemClick(e){
        e.preventDefault();
        window.location = e.target.closest('a').href;
        return;
    }

    /**
     * Handles interaction with the mobile nav button, opening the sidebar
     * @param {*} e 
     */
    handleNavToggleClick(e) {      
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('open');
    }

    closeNav(e) {
        document.getElementById('sidebar').classList.remove('open');
    }

    changeLang(event) {
        window.location.href = '/settings/theme/kbin_lang/' + event.target.value;
    }
}
