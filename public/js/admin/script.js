//attach handlers to menu
function handleMenu(){
    var menu = document.getElementById('menu');
    var lis = menu.getElementsByTagName('li');
    for(var i=0; i<lis.length; i++){
        lis[i].addEventListener('click', dropm.mopen, false);
        lis[i].addEventListener('mouseover', dropm.mcancelclosetime, false);
        lis[i].addEventListener('mouseout', dropm.mclosetime, false);
    }
}

//--drop-down start--
var timeout = 500;
var closetimer = 0;
var menuitem = 0;

dropm = {
    // open hidden layer
    mopen: function(oEvent){
        oEvent.stopPropagation(); //prevents the body click from triggering the close timer
        // cancel close timer
        dropm.mcancelclosetime();

        // close old layer
        if(menuitem) menuitem.style.display = 'none';

        // get new layer and show it
        menuitem = document.getElementById(oEvent.target.rel);
        //menuitem.style.display = 'block'; //to comment out if use jquery
        $(menuitem).slideDown("normal", 'easeOutExpo');  //to use with jquery effects

    },
    // close showed layer
    mclose: function(){
        //if(menuitem) menuitem.style.display = 'none'; //to comment out if use jquery
        if(menuitem) $(menuitem).delay(800).slideUp("normal", 'easeOutExpo'); //to use with jquery effects
    },

    // go close timer
    mclosetime: function(){
        closetimer = window.setTimeout(dropm.mclose, timeout);
    },

    // cancel close timer
    mcancelclosetime: function(){
        if(closetimer)
        {
            window.clearTimeout(closetimer);
            closetimer = null;
        }
    }

// close layer when click-out

};

document.onclick = dropm.mclose;
//--drop-down end--

var image;
//image hover
img_change = {
    oldImg: '',
    ov: function(id, image){
        this.oldImg = id.src;
        id.src = image + '-ov.png';
    },
    out: function(id){
        id.src = this.oldImg;
    }
};