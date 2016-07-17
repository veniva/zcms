viewModel.filter = ko.observable(0);
//viewModel.fileData().file() - contains file info
viewModel.fileData = ko.observable({
    base64String: ko.observable() //only base64 data
//        dataURL: ko.observable() //the entire string including metadata
});
rest.config({
    listParams: {
        filter: viewModel.filter()
    },
    getAddParams: {
        filter: viewModel.filter()
    }
});

rest.addRoutes(function(sammyApp){
    sammyApp.post('#delete', function(){
        var self = this;
        var formData = {};
        for(var prop in this.params){
            if(this.params.hasOwnProperty(prop)) formData[prop] = this.params[prop];
        }
        lib.overlay();
        $.post(viewModel.url+'/deleteAjax', formData, function(data){
            self.redirect(viewModel.url+'/list#'+viewModel.page());
            viewModel.flashMessages([{type: data.message.type, message: data.message.text}]);
        });
    });

    sammyApp.bind('run-route', function() {
        if (this.params.verb == 'get') {//the image should stay when data is put/posted to the server
            if(viewModel.fileData().base64String instanceof Function && viewModel.fileData().base64String())
                viewModel.fileData().base64String(null);
            if(viewModel.fileData().file instanceof Function  && viewModel.fileData().file())
                viewModel.fileData().file(null);
        }
    });
});

viewModel.fileData().base64String.subscribe(function(base64String){
    if(base64String){
        var listingImage = {
            base64: base64String,
            name: viewModel.fileData().file().name
        };
        rest.putEditParams.listing_image = listingImage;
        rest.postAddParams.listing_image = listingImage;
    }else{
        rest.putEditParams.listing_image = null;
        rest.postAddParams.listing_image = null;
    }
});

$('#filter_category').change(function(){
    viewModel.filter(this.value);
    rest.config({
        listParams: {
            filter: viewModel.filter()
        },
        getAddParams: {
            filter: viewModel.filter()
        }
    });
    lib.get(viewModel.url,{
        filter: viewModel.filter(),
        page: viewModel.page()
    }, function(data){
        rest.handleListData(data);
    });
});