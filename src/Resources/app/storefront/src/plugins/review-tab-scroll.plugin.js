import Plugin from 'src/plugin-system/plugin.class';

export default class ReviewTabScrollPlugin extends Plugin {
    init() {
        this.linkRefButton = this.el.querySelector('.btn');
        this.reviewTabLink = document.querySelector('.nav-link.review-tab');

        if (this.linkRefButton && this.reviewTabLink) {
            this._registerEvents();
        }
    }

    _registerEvents() {
        this.linkRefButton.addEventListener('click', (event) => {
            event.preventDefault();

            // Trigger tab click
            this.reviewTabLink.click();

            // Smooth scroll to tab
            this.reviewTabLink.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        });
    }
}
