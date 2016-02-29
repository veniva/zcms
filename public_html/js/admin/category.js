(function (){

    viewModel.action = ko.observable();
    viewModel.title = ko.observable();
    viewModel.page = ko.observable();
    viewModel.url = document.getElementById('url').textContent;

    //list related
    viewModel.categoryData = ko.observableArray();
    viewModel.hasRecords = ko.observable(null);
    viewModel.paginator = ko.observable();
    viewModel.breadcrumb = ko.observable();
    viewModel.parentID = ko.observable();

    //edit related
    viewModel.form = ko.observable();

    var handleListData = function(data){
        lib.showMessages();
        viewModel.action('list');
        viewModel.title(data.title);

        if(data.categories.length){
            viewModel.categoryData(data.categories);
            viewModel.hasRecords(true);
        }else{
            viewModel.hasRecords(false)
        }
        viewModel.page(data.page);
        viewModel.paginator(data.paginator);
        viewModel.breadcrumb(data.breadcrumb);
        viewModel.parentID(data.parent_id);
    };

    Sammy(function(){
        this.get(viewModel.url, function(){
            lib.get(viewModel.url+'/listJson', function(data){
                handleListData(data);
            });
        });

        this.get('#:id/:page', function(){
            lib.get(viewModel.url+'/listJson/'+this.params.id+'/'+this.params.page, function(data){
                handleListData(data);
            });
        });

        this.get('#:action/:id/:page', function(){
            var action = this.params.action;
            viewModel.action(action);
            viewModel.title('');
            viewModel.page('');
            viewModel.form('');
            lib.get(viewModel.url+'/'+action+'Json/'+this.params.id+'/'+this.params.page, function(data){
                if(typeof data.message == 'object'){
                    self.redirect(viewModel.url+'#'+data.parent_id+'/'+self.params.page);
                    viewModel.flashMessages([{type: data.message.type, message: data.message.text}]);
                }else{
                    viewModel.title(data.title);
                    viewModel.page(data.page);
                    viewModel.form(data.form);
                }
            });
        });

        this.post('#:action/:id/:page', function(){
            var self = this;
            var action = self.params.action;
            var postedData = {};
            for(var prop in self.params){
                if(self.params.hasOwnProperty(prop)) postedData[prop] = self.params[prop];
            }
            lib.overlay();
            $.post(viewModel.url+'/'+action+'Json/'+self.params.id+'/'+self.params.page, postedData, function(data){
                if(typeof data.message == 'object'){
                    self.redirect(viewModel.url+'#'+data.parent_id+'/'+self.params.page);
                    viewModel.flashMessages([{type: data.message.type, message: data.message.text}]);
                }else{
                    viewModel.title(data.title);
                    viewModel.page(data.page);
                    viewModel.form(data.form);
                    lib.removeOverlay();
                }
            });
        });

        this.bind('run-route', function() {
            $('.alert').alert('close').on('closed.bs.alert', function () {
                viewModel.messages([]);
                viewModel.flashMessages([]);
            });
        });

    }).run(viewModel.url);
})();