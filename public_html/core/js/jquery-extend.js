jQuery.fn.center = function () {
    this.css('position','fixed');
    this.css('top', '50%');
    this.css('left', '50%');
    this.css('margin-top', -(this.outerHeight() / 2) + 'px');
    this.css('margin-left', -(this.outerWidth() / 2) + 'px');

    return this;
};

jQuery.fn.showCentered = function($elem, speed){
    if(!$elem || $elem instanceof jQuery == false) return;
    if(this.find('.modalContent').length > 0) return;//prevent repetitions

    $elem.addClass('modalContent').addClass('ui-helper-hidden').css({ "z-index": 1002 });
    this.append($elem);
    $elem.center();
    $elem.fadeIn(speed || 400);
};

jQuery.fn.hideCentered = function(speed){
    if(speed != 0){
        this.find('.modalContent').fadeOut(speed || maskSpeed, function(){
            this.remove();
        });
    }else{
        this.find('.modalContent').remove();
    }

};

//loading mask based on http://jsfiddle.net/larsolesimonsen/7dKaq/23/

var maskSpeed = 400;
jQuery.fn.showMask = function(speed){
    if(this.find('.modalOverlay').length > 0) return;//prevent repetitions

    var mask = document.createElement('div');
    mask.className = "modalOverlay ui-widget-overlay ui-helper-hidden";
    mask.style.position = 'fixed';
    mask.style.zIndex = 1002;
    this.append(mask);
    $(mask).fadeIn(speed || 400);
};

jQuery.fn.hideMask = function(speed){
    if(speed != 0){
        this.find('.modalOverlay').fadeOut(speed || maskSpeed, function(){
            this.remove();
        });
    }else{
        this.find('.modalOverlay').remove();
    }

};

jQuery.fn.showLoadingMask = function(speed){
    this.showMask(speed);
    this.showCenteredProgress(speed);
};

jQuery.fn.hideLoadingMask = function(speed){
    this.hideMask(speed);
    this.hideCentered(speed);
};

jQuery.fn.showCenteredProgress = function(speed){
    var $progressBar = $(document.createElement('div'));
    $progressBar.css({
        border: 'none',
        padding: 0,
        "border-radius": '4px'
    });
    $progressBar.addClass('col-xs-2');
    $progressBar.progressbar({value:false});
    $progressBar.find( ".ui-progressbar-value" ).css({ margin: 0,  "border-radius": '4px'});
    this.showCentered($progressBar, speed);
};

/**
 * Show centered message based on jQuery dialog widget
 * @param {string} text alert message
 * @param {object} cssClass One of the following: default, highlight, error
 * @param {string} button
 * @param {int} speed
 * @returns {object}
 */
jQuery.fn.showCenteredMessage = function(text, cssClass, button, speed){
    if(!text) return;
    cssClass = cssClass ? cssClass : 'default';
    var icon = '';
    var letterColor = 'black';
    button = !button ? '' : '<p style="text-align: right">'+button+'</p>';

    switch(cssClass){
        case 'highlight':
        case 'error':
            icon = 'exclamation-sign';
            letterColor = 'red';
            break;
        default:
            icon = 'info-sign';
    }
    var $container = $(document.createElement('div'));
    $container.html('<p style="min-height: 36px;padding: 10px;margin-top: 10px; color: '+letterColor+'"><span class="glyphicon glyphicon-'+icon+'" aria-hidden="true"></span> &nbsp;'+text+'</p>'+button);
    $container.dialog({
        modal: true,
        title: 'Информация',
        show: {
            duration: speed || maskSpeed
        },
        hide: {
            duration: speed || maskSpeed
        }
    });

    //additional styles to the dialog window
    var titleBar = $container.siblings()[0];
    $(titleBar).addClass('ui-state-'+cssClass);//add class to the title bar
    var $button = $(titleBar).find('button');
    $button.mouseover(function(){$(this).removeClass('ui-state-hover');});//remove the hover background of the close button
    $button.mouseup(function(){$(this).removeClass('ui-state-focus');});//remove the hover background of the close button
    $button.focus(function(){$(this).removeClass('ui-state-focus');});//remove the hover background of the close button
    return $container;
};

//region ================== responsive dialog window ================== //

//http://jsfiddle.net/jasonday/nWcFR/

// add new options with default values
$.ui.dialog.prototype.options.clickOut = true;
$.ui.dialog.prototype.options.responsive = true;
$.ui.dialog.prototype.options.scaleH = 0.8;
$.ui.dialog.prototype.options.scaleW = 0.8;
$.ui.dialog.prototype.options.showTitleBar = true;
$.ui.dialog.prototype.options.showCloseButton = true;

var resize = function(){};

// extend open function
var _open = $.ui.dialog.prototype.open;
var $dialogOverlay = $('#dialog-overlay');
$.ui.dialog.prototype.open = function () {
    var self = this;

    // apply original arguments
    _open.apply(this, arguments);

    // get dialog original size on open
    var oHeight = self.element.parent().outerHeight(),
        oWidth = self.element.parent().outerWidth(),
        isTouch = $("html").hasClass("touch");

    // responsive width & height
    resize = function () {

        //check if responsive
        // dependent on modernizr for device detection / html.touch
        if (self.options.responsive === true || (self.options.responsive === "touch" && isTouch)) {
            var elem = self.element,
                wHeight = $(window).height(),
                wWidth = $(window).width(),
                setHeight = Math.min(wHeight * self.options.scaleH, oHeight),
                setWidth = Math.min(wWidth * self.options.scaleW, oWidth);

            if ((oHeight + 100) > wHeight || elem.hasClass("resizedH") || (oHeight + 100) < wHeight) {
                elem.dialog("option", "height", setHeight).parent().css("max-height", setHeight);
                elem.addClass("resizedH");
            }
            if ((oWidth + 100) > wWidth || elem.hasClass("resizedW")) {
                elem.dialog("option", "width", setWidth).parent().css("max-width", setWidth);
                elem.addClass("resizedW");
            }

            elem.dialog("option", "position", { my: "center", at: "center", of: window });
            elem.css("overflow", "auto");
        }

        // add webkit scrolling to all dialogs for touch devices
        if (isTouch) {
            elem.css("-webkit-overflow-scrolling", "touch");
        }
    };

    // call resize()
    resize();

    // resize on window resize
    window.addEventListener("resize", resize);

    // resize on orientation change
    window.addEventListener("orientationchange", resize);

    // hide titlebar
    if (!self.options.showTitleBar) {
        self.uiDialogTitlebar.css({
            "height": 0,
            "padding": 0,
            "background": "none",
            "border": 0
        });
        self.uiDialogTitlebar.find(".ui-dialog-title").css("display", "none");
    }

    //hide close button
    if (!self.options.showCloseButton) {
        self.uiDialogTitlebar.find(".ui-dialog-titlebar-close").css("display", "none");
    }

    // close on clickOut
    if (self.options.clickOut && !self.options.modal) {
        // use transparent div - simplest approach (rework)
        $('<div id="dialog-overlay"></div>').insertBefore(self.element.parent());

        $dialogOverlay.css({
            "position": "fixed",
            "top": 0,
            "right": 0,
            "bottom": 0,
            "left": 0,
            "background-color": "transparent"
        });
        $dialogOverlay.click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            self.close();
        });
        // else close on modal click
    } else if (self.options.clickOut && self.options.modal) {
        $('.ui-widget-overlay').click(function (e) {
            self.close();
        });
    }

    // add dialogClass to overlay
    if (self.options.dialogClass) {
        $('.ui-widget-overlay').addClass(self.options.dialogClass);
    }
};
//end open

// extend close function
var _close = $.ui.dialog.prototype.close;
$.ui.dialog.prototype.close = function () {
    var self = this;
    // apply original arguments
    _close.apply(this, arguments);

    // remove dialogClass to overlay
    if (self.options.dialogClass) {
        $('.ui-widget-overlay').removeClass(self.options.dialogClass);
    }
    //remove clickOut overlay
    if ($dialogOverlay.length) {
        $dialogOverlay.remove();
    }
    window.removeEventListener('resize', resize);
    window.removeEventListener('orientationchange', resize);
};
//end close

//endregion