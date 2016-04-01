(function (){
    viewModel.breadcrumb = ko.observable();
    viewModel.parentID = ko.observable(0);

    var setParentID = function(newId){
        rest.config({
            getAddParams: {parent_id: newId},
            postAddParams: {parent_id: newId}
        });
    };
    setParentID(viewModel.parentID());
    viewModel.parentID.subscribe(function(newValue){
        setParentID(newValue);
    });

    rest.setHandleMoreListData(function(data){
        viewModel.breadcrumb(data.breadcrumb);
        viewModel.parentID(data.parent_id);
    });
    rest.addRoutes(function(sammyApp){

        sammyApp.get(/#([\d]+)\/([\d]+)/, function(){
            var page = this.params['splat'][1];
            viewModel.page(page);
            lib.get(viewModel.url, { parent_id: this.params['splat'][0], page:  page}, function(data){
                rest.handleListData(data);
                rest.handleMoreListData(data);
            });
        });

        sammyApp.post('#delete/:id', function () {
            var sammyEventContext = this;
            lib.overlay();
            $.ajax({
                method: 'DELETE',
                url: viewModel.url + '/' + this.params['id'],
                success: function (data) {
                    sammyEventContext.redirect(viewModel.url + '/list#' + data.parent_id + '/' + viewModel.page());
                    viewModel.flashMessages([{type: data.message.type, message: data.message.text}]);
                }
            });
        });
    });
    rest.setHandleAddEditResponse(function(data, self, removeOverlay){
        if (typeof data.message == 'object') {
            viewModel.flashMessages([{type: data.message.type, message: data.message.text}]);
            if(!data.message.no_redir){
                self.redirect(viewModel.url + '/list#'+data.parent_id + '/' + viewModel.page());
            }else{
                viewModel.title(data.title);
                viewModel.form(data.form);
                if(removeOverlay) lib.removeOverlay();
                lib.showMessages();
            }
        } else {
            viewModel.title(data.title);
            viewModel.form(data.form);
            if (removeOverlay) lib.removeOverlay();
        }
        viewModel.parentID(data.parent_id);
    });
})();