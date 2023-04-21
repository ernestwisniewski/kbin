Util = function() {};

Util.hasClass = function(el, className) {
    return el.classList.contains(className);
};

Util.addClass = function(el, className) {
    var classList = className.split(' ');
    el.classList.add(classList[0]);
    if (classList.length > 1) Util.addClass(el, classList.slice(1).join(' '));
};

Util.removeClass = function(el, className) {
    var classList = className.split(' ');
    el.classList.remove(classList[0]);
    if (classList.length > 1) Util.removeClass(el, classList.slice(1).join(' '));
};

Util.toggleClass = function(el, className, bool) {
    if(bool) Util.addClass(el, className);
    else Util.removeClass(el, className);
};

Util.setAttributes = function(el, attrs) {
    for(var key in attrs) {
        el.setAttribute(key, attrs[key]);
    }
};

Util.moveFocus = function (element) {
    if( !element ) element = document.getElementsByTagName('body')[0];
    element.focus();
    if (document.activeElement !== element) {
        element.setAttribute('tabindex','-1');
        element.focus();
    }
};

// Usage: codyhouse.co/license
(function() {
    var Popover = function(element) {
        this.element = element;
        this.elementId = this.element.getAttribute('id');
        this.trigger = document.querySelectorAll('[aria-controls="'+this.elementId+'"]');
        this.selectedTrigger = false;
        this.popoverVisibleClass = 'popover--is-visible';
        this.selectedTriggerClass = 'popover-control--active';
        this.popoverIsOpen = false;
        // focusable elements
        this.firstFocusable = false;
        this.lastFocusable = false;
        // position target - position tooltip relative to a specified element
        this.positionTarget = getPositionTarget(this);
        // gap between element and viewport - if there's max-height
        this.viewportGap = parseInt(getComputedStyle(this.element).getPropertyValue('--popover-viewport-gap')) || 20;
        initPopover(this);
        initPopoverEvents(this);
    };

    // public methods
    Popover.prototype.togglePopover = function(bool, moveFocus) {
        togglePopover(this, bool, moveFocus);
    };

    Popover.prototype.checkPopoverClick = function(target) {
        checkPopoverClick(this, target);
    };

    Popover.prototype.checkPopoverFocus = function() {
        checkPopoverFocus(this);
    };

    // private methods
    function getPositionTarget(popover) {
        // position tooltip relative to a specified element - if provided
        var positionTargetSelector = popover.element.getAttribute('data-position-target');
        if(!positionTargetSelector) return false;
        var positionTarget = document.querySelector(positionTargetSelector);
        return positionTarget;
    };

    function initPopover(popover) {
        // reset popover position
        initPopoverPosition(popover);
        // init aria-labels
        for(var i = 0; i < popover.trigger.length; i++) {
            Util.setAttributes(popover.trigger[i], {'aria-expanded': 'false', 'aria-haspopup': 'true'});
        }
    };

    function initPopoverEvents(popover) {
        for(var i = 0; i < popover.trigger.length; i++) {(function(i){
            popover.trigger[i].addEventListener('click', function(event){
                event.preventDefault();
                // if the popover had been previously opened by another trigger element -> close it first and reopen in the right position
                if(Util.hasClass(popover.element, popover.popoverVisibleClass) && popover.s !=  popover.trigger[i]) {
                    togglePopover(popover, false, false); // close menu
                }
                // toggle popover
                popover.selectedTrigger = popover.trigger[i];
                togglePopover(popover, !Util.hasClass(popover.element, popover.popoverVisibleClass), true);
            });
        })(i);}

        // trap focus
        popover.element.addEventListener('keydown', function(event){
            if( event.keyCode && event.keyCode == 9 || event.key && event.key == 'Tab' ) {
                //trap focus inside popover
                trapFocus(popover, event);
            }
        });

        // custom events -> open/close popover
        popover.element.addEventListener('openPopover', function(event){
            togglePopover(popover, true);
        });

        popover.element.addEventListener('closePopover', function(event){
            togglePopover(popover, false, event.detail);
        });
    };

    function togglePopover(popover, bool, moveFocus) {
        // toggle popover visibility
        Util.toggleClass(popover.element, popover.popoverVisibleClass, bool);
        popover.popoverIsOpen = bool;
        if(bool) {
            popover.selectedTrigger.setAttribute('aria-expanded', 'true');
            getFocusableElements(popover);
            // move focus
            focusPopover(popover);
            popover.element.addEventListener("transitionend", function(event) {focusPopover(popover);}, {once: true});
            // position the popover element
            positionPopover(popover);
            // add class to popover trigger
            Util.addClass(popover.selectedTrigger, popover.selectedTriggerClass);
        } else if(popover.selectedTrigger) {
            popover.selectedTrigger.setAttribute('aria-expanded', 'false');
            if(moveFocus) Util.moveFocus(popover.selectedTrigger);
            // remove class from menu trigger
            Util.removeClass(popover.selectedTrigger, popover.selectedTriggerClass);
            popover.selectedTrigger = false;
        }
    };

    function focusPopover(popover) {
        if(popover.firstFocusable) {
            popover.firstFocusable.focus();
        } else {
            Util.moveFocus(popover.element);
        }
    };

    function positionPopover(popover) {
        // reset popover position
        resetPopoverStyle(popover);
        var selectedTriggerPosition = (popover.positionTarget) ? popover.positionTarget.getBoundingClientRect() : popover.selectedTrigger.getBoundingClientRect();

        var menuOnTop = (window.innerHeight - selectedTriggerPosition.bottom) < selectedTriggerPosition.top;

        var left = selectedTriggerPosition.left,
            right = (window.innerWidth - selectedTriggerPosition.right),
            isRight = (window.innerWidth < selectedTriggerPosition.left + popover.element.offsetWidth);

        var horizontal = isRight ? 'right: '+right+'px;' : 'left: '+left+'px;',
            vertical = menuOnTop
                ? 'bottom: '+(window.innerHeight - selectedTriggerPosition.top)+'px;'
                : 'top: '+selectedTriggerPosition.bottom+'px;';
        // check right position is correct -> otherwise set left to 0
        if( isRight && (right + popover.element.offsetWidth) > window.innerWidth) horizontal = 'left: '+ parseInt((window.innerWidth - popover.element.offsetWidth)/2)+'px;';
        // check if popover needs a max-height (user will scroll inside the popover)
        var maxHeight = menuOnTop ? selectedTriggerPosition.top - popover.viewportGap : window.innerHeight - selectedTriggerPosition.bottom - popover.viewportGap;

        var initialStyle = popover.element.getAttribute('style');
        if(!initialStyle) initialStyle = '';
        popover.element.setAttribute('style', initialStyle + horizontal + vertical +'max-height:'+Math.floor(maxHeight)+'px;');
    };

    function resetPopoverStyle(popover) {
        // remove popover inline style before appling new style
        popover.element.style.maxHeight = '';
        popover.element.style.top = '';
        popover.element.style.bottom = '';
        popover.element.style.left = '';
        popover.element.style.right = '';
    };

    function initPopoverPosition(popover) {
        // make sure the popover does not create any scrollbar
        popover.element.style.top = '0px';
        popover.element.style.left = '0px';
    };

    function checkPopoverClick(popover, target) {
        // close popover when clicking outside it
        if(!popover.popoverIsOpen) return;
        if(!popover.element.contains(target) && !target.closest('[aria-controls="'+popover.elementId+'"]')) togglePopover(popover, false);
    };

    function checkPopoverFocus(popover) {
        // on Esc key -> close popover if open and move focus (if focus was inside popover)
        if(!popover.popoverIsOpen) return;
        var popoverParent = document.activeElement.closest('.js-popover');
        togglePopover(popover, false, popoverParent);
    };

    function getFocusableElements(popover) {
        //get all focusable elements inside the popover
        var allFocusable = popover.element.querySelectorAll(focusableElString);
        getFirstVisible(popover, allFocusable);
        getLastVisible(popover, allFocusable);
    };

    function getFirstVisible(popover, elements) {
        //get first visible focusable element inside the popover
        for(var i = 0; i < elements.length; i++) {
            if( isVisible(elements[i]) ) {
                popover.firstFocusable = elements[i];
                break;
            }
        }
    };

    function getLastVisible(popover, elements) {
        //get last visible focusable element inside the popover
        for(var i = elements.length - 1; i >= 0; i--) {
            if( isVisible(elements[i]) ) {
                popover.lastFocusable = elements[i];
                break;
            }
        }
    };

    function trapFocus(popover, event) {
        if( popover.firstFocusable == document.activeElement && event.shiftKey) {
            //on Shift+Tab -> focus last focusable element when focus moves out of popover
            event.preventDefault();
            popover.lastFocusable.focus();
        }
        if( popover.lastFocusable == document.activeElement && !event.shiftKey) {
            //on Tab -> focus first focusable element when focus moves out of popover
            event.preventDefault();
            popover.firstFocusable.focus();
        }
    };

    function isVisible(element) {
        // check if element is visible
        return element.offsetWidth || element.offsetHeight || element.getClientRects().length;
    };

    window.Popover = Popover;

    //initialize the Popover objects
    var popovers = document.getElementsByClassName('js-popover');
    // generic focusable elements string selector
    var focusableElString = '[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), iframe, object, embed, [tabindex]:not([tabindex="-1"]), [contenteditable], audio[controls], video[controls], summary';

    if( popovers.length > 0 ) {
        var popoversArray = [];
        var scrollingContainers = [];
        for( var i = 0; i < popovers.length; i++) {
            (function(i){
                popoversArray.push(new Popover(popovers[i]));
                var scrollableElement = popovers[i].getAttribute('data-scrollable-element');
                if(scrollableElement && !scrollingContainers.includes(scrollableElement)) scrollingContainers.push(scrollableElement);
            })(i);
        }

        // listen for key events
        window.addEventListener('keyup', function(event){
            if( event.keyCode && event.keyCode == 27 || event.key && event.key.toLowerCase() == 'escape' ) {
                // close popover on 'Esc'
                popoversArray.forEach(function(element){
                    element.checkPopoverFocus();
                });
            }
        });
        // close popover when clicking outside it
        window.addEventListener('click', function(event){
            popoversArray.forEach(function(element){
                element.checkPopoverClick(event.target);
            });
        });
        // on resize -> close all popover elements
        window.addEventListener('resize', function(event){
            popoversArray.forEach(function(element){
                element.togglePopover(false, false);
            });
        });
        // on scroll -> close all popover elements
        window.addEventListener('scroll', function(event){
            popoversArray.forEach(function(element){
                if(element.popoverIsOpen) element.togglePopover(false, false);
            });
        });
        // take into account additinal scrollable containers
        for(var j = 0; j < scrollingContainers.length; j++) {
            var scrollingContainer = document.querySelector(scrollingContainers[j]);
            if(scrollingContainer) {
                scrollingContainer.addEventListener('scroll', function(event){
                    popoversArray.forEach(function(element){
                        if(element.popoverIsOpen) element.togglePopover(false, false);
                    });
                });
            }
        }
    }

    window.popover = popoversArray[0];
}());
