(function(){
    viewModel.action = ko.observable();
    viewModel.title = ko.observable();
    viewModel.page = ko.observable();
    viewModel.url = document.getElementById('url').textContent;

    //list related
    viewModel.listData = ko.observableArray();
    viewModel.hasRecords = ko.observable(null);
    viewModel.paginator = ko.observable();
    viewModel.various = ko.observable();

    //edit related
    viewModel.form = ko.observable();

    var handleListData = function(data){
        lib.showMessages();
        viewModel.action('list');
        viewModel.title(data.title);
        if(data.various)
            viewModel.various(data.various);

        if(data.lists && data.lists.length){
            viewModel.listData(data.lists);
            viewModel.hasRecords(true);
        }else{
            viewModel.hasRecords(false)
        }
        viewModel.page(data.page);
        viewModel.paginator(data.paginator);
        viewModel.form(null);
    };

    var handleAddEditResponse = function(data, self, removeOverlay){
        if(typeof data.message == 'object'){
            self.redirect(viewModel.url+'/list#'+self.params['page']);
            viewModel.flashMessages([{type: data.message.type, message: data.message.text}]);
        }else{
            viewModel.title(data.title);
            viewModel.page(self.params['page']);
            viewModel.form(data.form);
            if(removeOverlay) lib.removeOverlay();
        }
    };

    Sammy(function(){
        //calls getList() with no page number
        this.get(viewModel.url+'/list', function(){
            lib.get(viewModel.url, function(data){
                handleListData(data);
            });
        });
        //calls getList() with page number
        this.get(viewModel.url+'/list#:page', function(){
            lib.get(viewModel.url+'/'+this.params.page, function(data){
                handleListData(data);
            });
        });

        //calls editJson() or addJson() to display add/edit form
        this.get('#:action/:page/:id', function(){
            var self = this;
            var action = this.params['action'];
            viewModel.action('edit');
            viewModel.title('');
            viewModel.page('');
            lib.get(viewModel.url+'/'+action+'Json/'+this.params['page']+'/'+this.params['id'], function(data){
                handleAddEditResponse(data, self);
            });
        });

        //calls update()
        this.post('#edit/:page/:id', function(){
            var self = this;
            var formData = {};
            for(var prop in this.params){
                if(this.params.hasOwnProperty(prop)) formData[prop] = this.params[prop];
            }
            lib.overlay();
            $.ajax({
                method: 'PUT',
                url: viewModel.url+'/'+this.params['page']+'/'+this.params['id'],
                data: formData,
                success: function(data){
                    handleAddEditResponse(data, self, true);
                }
            });
        });

        //calls create()
        this.post('#add/:page', function(){
            var self = this;
            var formData = {};
            for(var prop in this.params){
                if(this.params.hasOwnProperty(prop)) formData[prop] = this.params[prop];
            }
            lib.overlay();
            $.post(viewModel.url+'/'+this.params['page'], formData, function(data){
                handleAddEditResponse(data, self, true);
            });
        });

        //calls delete()
        this.post('#delete/:page/:id', function(){
            var self = this;
            lib.overlay();
            $.ajax({
                method: 'DELETE',
                url: viewModel.url+'/'+this.params['page']+'/'+this.params['id'],
                success: function(data){
                    self.redirect(viewModel.url+'/list#'+self.params['page']);
                    viewModel.flashMessages([{type: data.message.type, message: data.message.text}]);
                }
            });
        });

        this.bind('run-route', function() {
            $('.alert').alert('close').on('closed.bs.alert', function () {
                viewModel.messages([]);
                viewModel.flashMessages([]);
            });
        });

    }).run(viewModel.url+'/list');
})();