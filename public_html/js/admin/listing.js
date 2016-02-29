(function(){
    viewModel.action = ko.observable();
    viewModel.title = ko.observable();
    viewModel.page = ko.observable(1);
    viewModel.url = document.getElementById('url').textContent;

    //list related
    viewModel.listData = ko.observableArray();
    viewModel.hasRecords = ko.observable(null);
    viewModel.paginator = ko.observable();

    //edit related
    viewModel.form = ko.observable();

    Sammy(function(){
        //calls getList() with no page number
        this.get(viewModel.url+'/list', function(){
            lib.get(viewModel.url, function(data){
                handleListData(data);
            });
        });
        //receives page number as parameter 'splat', calls getListAction()
        this.get(/#([\d]+)/, function(){
            var page = this.params['splat'][0];
            viewModel.page(page);
            getList(page);
        });

        //calls editJson() to display edit form
        this.get('#edit/:id', function(){
            var self = this;
            viewModel.action('edit');
            viewModel.title(null);
            lib.get(viewModel.url+'/editJson/', {
                id: this.params['id']
            }, function(data){
                handleAddEditResponse(data, self);
            });
        });

        //calls addJson() to display add form
        this.get('#add', function(){
            var self = this;
            viewModel.action('add');
            viewModel.title(null);
            lib.get(viewModel.url+'/addJson/', {
                filter: viewModel.filter()
            }, function(data){
                handleAddEditResponse(data, self);
            });
        });

        //calls update()
        this.post('#edit/:id', function(){
            var self = this;
            var formData = {};
            for(var prop in this.params){
                if(this.params.hasOwnProperty(prop)) formData[prop] = this.params[prop];
            }
            if(viewModel.fileData().file())
                formData.listing_image = {'base64': viewModel.fileData().base64String(), name: viewModel.fileData().file().name};
            lib.overlay();
            $.ajax({
                method: 'PUT',
                url: viewModel.url+'/'+this.params['id'],
                data: formData,
                success: function(data){
                    handleAddEditResponse(data, self, true);
                }
            });
        });

        //calls create()
        this.post('#add', function(){
            var self = this;
            var formData = {};
            for(var prop in this.params){
                if(this.params.hasOwnProperty(prop)) formData[prop] = this.params[prop];
            }
            formData.filter = viewModel.filter();
            if(viewModel.fileData().file())
                formData.listing_image = {'base64': viewModel.fileData().base64String(), name: viewModel.fileData().file().name};
            lib.overlay();
            $.post(viewModel.url, formData, function(data){
                handleAddEditResponse(data, self, true);
            });
        });

        //calls deleteJson()
        this.post('#delete', function(){
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

        this.bind('run-route', function() {
            if(this.params.verb == 'get'){//the image should stay when data is put/posted to the server
                viewModel.fileData = ko.observable({
                    base64String: ko.observable(), //unload image data
                    file: ko.observable()
                });

            }

            $('.alert').alert('close').on('closed.bs.alert', function () {
                viewModel.messages([]);
                viewModel.flashMessages([]);
            });
        });

    }).run(viewModel.url+'/list');
})();