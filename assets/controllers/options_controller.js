// SPDX-FileCopyrightText: 2023 /kbin contributors <https://kbin.pub/en\>
//
// SPDX-License-Identifier: AGPL-3.0-only

import {ActionEvent, Controller} from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['federation', 'settings', 'actions'];
    static values = {
        activeTab: String
    }

    connect() {
        const activeTabFragment = window.location.hash;

        if (!activeTabFragment || !['#federation', '#settings'].includes(activeTabFragment)) {
            return;
        }

        this.actionsTarget.querySelector(`a[href="${activeTabFragment}"]`).classList.add('active');

        this.activeTabValue = activeTabFragment.substring(1);
    }

    /** @param {ActionEvent} e */
    toggleTab(e) {
        const selectedTab = e.params.tab;

        this.actionsTarget.querySelectorAll('.active').forEach(el => el.classList.remove('active'));

        if (selectedTab === this.activeTabValue) {
            this.activeTabValue = 'none';
        } else {
            this.activeTabValue = selectedTab;

            e.currentTarget.classList.add('active');
        }
    }

    activeTabValueChanged(selectedTab) {
        if (selectedTab === 'none') {
            this.federationTarget.style.display = 'none';
            this.settingsTarget.style.display = 'none';

            return;
        }

        this[`${selectedTab}Target`].style.display = 'block';

        const otherTab = selectedTab === 'settings' ? 'federation' : 'settings';

        this[`${otherTab}Target`].style.display = 'none';
    }

    closeMobileSidebar() {
        document.getElementById('sidebar').classList.remove('open');
    }
}