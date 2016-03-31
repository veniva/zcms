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