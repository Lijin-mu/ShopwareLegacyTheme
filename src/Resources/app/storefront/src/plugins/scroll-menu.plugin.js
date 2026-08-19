import Plugin from 'src/plugin-system/plugin.class';

export default class MenuScrollerPlugin extends Plugin {
    static options = {
        activeItemSelector: '.main-navigation-link.active',
        listSelector: '.main-navigation-menu-list',
        wrapperClass: 'js--menu-scroller',
        listClass: 'js--menu-scroller--list',
        itemClass: 'js--menu-scroller--item',
        leftArrowClass: 'js--menu-scroller--arrow left--arrow',
        rightArrowClass: 'js--menu-scroller--arrow right--arrow',
        arrowContentClass: 'arrow--content',
        leftArrowContent: '&#58897;',
        rightArrowContent: '&#58895;',
        scrollStep: 'auto',
        animationSpeed: 400,
        arrowOffset: 25,
        disableScrollBarUpdate: false
    };

    init() {
        this.list = this.el.querySelector(this.options.listSelector);
        if (!this.list) return;
        
        setTimeout(() => {
            const activeChild = this.list.querySelector(this.options.activeItemSelector);
          if (activeChild) {
                this._jumpToElement(activeChild);
            }
        }, 500);

        this.scrollStep =
            this.options.scrollStep === 'auto'
                ? this.el.offsetWidth / 2
                : parseFloat(this.options.scrollStep);

        this.scrollBarOffset = 0;

        this._initTemplate();
        this._registerEvents();
        this._updateButtons();
    }

    _initTemplate() {
        const opts = this.options;
        this.el.classList.add(opts.wrapperClass);
        this.list.classList.add(opts.listClass);
        this.list.querySelectorAll(':scope > *').forEach(el => el.classList.add(opts.itemClass));

        this._updateScrollBarOffset();

        this.leftArrow = this._createArrow(opts.leftArrowClass, opts.arrowContentClass, opts.leftArrowContent);
        this.rightArrow = this._createArrow(opts.rightArrowClass, opts.arrowContentClass, opts.rightArrowContent);

        this.el.appendChild(this.leftArrow);
        this.el.appendChild(this.rightArrow);
    }

    _createArrow(arrowClass, contentClass, html) {
        const arrow = document.createElement('div');
        arrow.className = arrowClass;

        const span = document.createElement('span');
        span.className = contentClass;
        span.innerHTML = html;

        arrow.appendChild(span);
        return arrow;
    }

    _updateScrollBarOffset() {
        if (this.options.disableScrollBarUpdate) return;

        const offset = (this.scrollBarOffset = Math.min(
            Math.abs(this.list.scrollHeight - this.list.offsetHeight) * -1,
            this.scrollBarOffset
        ));

        this.list.style.bottom = `${offset}px`;
        this.list.style.marginTop = `${offset}px`;
    }

    _registerEvents() {
        this._resizeHandler = this._updateResize.bind(this);
        window.addEventListener('resize', this._resizeHandler);

        this.leftArrow.addEventListener('click', e => this._onLeftArrowClick(e));
        this.rightArrow.addEventListener('click', e => this._onRightArrowClick(e));
        this.list.addEventListener('scroll', () => this._updateButtons());
    }

    _updateResize() {
        this._updateScrollBarOffset();
        if (this.options.scrollStep === 'auto') {
            this.scrollStep = this.el.offsetWidth / 2;
        }
        this._updateButtons();
    }

    _onLeftArrowClick(event) {
        event.preventDefault();
        this._addOffset(this.scrollStep * -1);
    }

    _onRightArrowClick(event) {
        event.preventDefault();
        this._addOffset(this.scrollStep);
    }

    _addOffset(offset) {
        this._setOffset(this.list.scrollLeft + offset, true);
    }

    _setOffset(offset, animate = true) {
        const maxWidth = this.list.scrollWidth - this.el.offsetWidth;
        const newPos = Math.max(0, Math.min(maxWidth, offset));

        if (animate) {
            this._animateScroll(this.list, newPos, this.options.animationSpeed);
        } else {
            this.list.scrollLeft = newPos;
            this._updateButtons();
        }
    }

    _animateScroll(element, to, duration) {
        const start = element.scrollLeft;
        const change = to - start;
        const startTime = performance.now();

        const animate = now => {
            const elapsed = now - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const ease = 0.5 - Math.cos(progress * Math.PI) / 2;

            element.scrollLeft = start + change * ease;

            if (progress < 1) {
                requestAnimationFrame(animate);
            } else {
                this._updateButtons();
            }
        };

        requestAnimationFrame(animate);
    }

    _updateButtons() {
        const elWidth = this.el.offsetWidth;
        const listWidth = this.list.scrollWidth;
        const scrollLeft = this.list.scrollLeft;

        this.leftArrow.style.display = scrollLeft > 0 ? 'block' : 'none';
        this.rightArrow.style.display =
            listWidth > elWidth && scrollLeft < listWidth - elWidth ? 'block' : 'none';
    }

    _jumpToElement(el) {
        const elWidth = this.el.offsetWidth;
        const scrollLeft = this.list.scrollLeft;
        const leftPos = el.offsetLeft;
        const rightPos = leftPos + el.offsetWidth;
        let newPos;

        if (leftPos > scrollLeft && rightPos > scrollLeft + elWidth) {
            newPos = rightPos - elWidth + this.options.arrowOffset;
        } else {
            newPos = Math.min(leftPos - this.leftArrow.offsetWidth, scrollLeft);
        }


        this._setOffset(newPos, false);
    }

    destroy() {
        window.removeEventListener('resize', this._resizeHandler);

        const opts = this.options;
        this.el.classList.remove(opts.wrapperClass);
        this.list.classList.remove(opts.listClass);
        this.list.style.bottom = '';
        this.list.style.marginTop = '';

        this.list.querySelectorAll(`.${opts.itemClass}`).forEach(el => {
            el.classList.remove(opts.itemClass);
        });

        this.leftArrow.remove();
        this.rightArrow.remove();
    }
}
