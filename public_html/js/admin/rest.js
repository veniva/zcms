viewModel.action = ko.observable();
viewModel.title = ko.observable();
viewModel.page = ko.observable(1);
viewModel.url = document.getElementById('url').textContent;

//list related
viewModel.listData = ko.observableArray();
viewModel.hasRecords = ko.observable(null);
viewModel.paginator = ko.observable();
viewModel.various = ko.observable();

//edit related
viewModel.form = ko.observable();

function Rest(sammyApp) {
    if(!(sammyApp instanceof Sammy.Application))
        throw 'invalid Sammy instance';

    this.sammyApp = sammyApp;
    this.listParams = {};
    this.getEditParams = {};
    this.putEditParams = {};
    this.getAddParams = {};
    this.postAddParams = {};
}
Rest.prototype.config = function (settings) {
    if(settings.listParams)
        this.listParams = settings.listParams;
    if(settings.getEditParams)
        this.getEditParams = settings.getEditParams;
    if(settings.putEditParams)
        this.putEditParams = settings.putEditParams;
    if(settings.getAddParams)
        this.getAddParams = settings.getAddParams;
    if(settings.postAddParams)
        this.postAddParams = settings.postAddParams;
};

Rest.prototype.getList = function () {
    var self = this;
    //calls getList() with no page number
    this.sammyApp.get(viewModel.url + '/list', function () {
        lib.get(viewModel.url, self.listParams, function (data) {
            self.handleListData(data);
            self.handleMoreListData(data);
        });
    });

    this.sammyApp.get(/#([\d]+)/, function () {
        var page = this.params['splat'][0];
        viewModel.page(page);
        var params = self.listParams;
        params.page = page;
        lib.get(viewModel.url, params, function (data) {
            self.handleListData(data);
            self.handleMoreListData(data);
        });
    });
};
Rest.prototype.getEdit = function () {
    var self = this;
    this.sammyApp.get('#edit/:id', function () {
        var sammyEventContext = this;
        viewModel.title(null);
        viewModel.action('edit');
        lib.get(viewModel.url + '/' + this.params['id'], self.getEditParams, function (data) {
            self.handleAddEditResponse(data, sammyEventContext);
            self.handleMoreAddEditResponse(data);
        });
    });
};
Rest.prototype.getAdd = function () {
    var self = this;
    this.sammyApp.get('#add', function () {
        var sammyEventContext = this;
        viewModel.action('add');
        viewModel.title(null);
        lib.get(viewModel.url + '/addJson', self.getAddParams, function (data) {
            self.handleAddEditResponse(data, sammyEventContext);
            self.handleMoreAddEditResponse(data);
        });
    });
};
Rest.prototype.putEdit = function () {
        var self = this;
        this.sammyApp.post('#edit/:id', function () {
            var sammyEventContext = this;
            var formData = self.putEditParams;
            for (var prop in this.params) {
                if (this.params.hasOwnProperty(prop)) formData[prop] = this.params[prop];
            }
            lib.overlay();
            $.ajax({
                method: 'PUT',
                url: viewModel.url + '/' + this.params['id'],
                data: formData,
                success: function (data) {
                    self.handleAddEditResponse(data, sammyEventContext, true);
                    self.handleMoreAddEditResponse(data);
                }
            });
        });
};
Rest.prototype.postAdd = function () {
    var self = this;
    this.sammyApp.post('#add', function () {
        var sammyEventContext = this;
        var formData = self.postAddParams;
        for (var prop in this.params) {
            if (this.params.hasOwnProperty(prop)) formData[prop] = this.params[prop];
        }
        lib.overlay();
        $.post(viewModel.url, formData, function (data) {
            self.handleAddEditResponse(data, sammyEventContext, true);
            self.handleMoreAddEditResponse(data);
        });
    });
};
Rest.prototype.del = function () {
    this.sammyApp.post('#delete/:id', function () {
        var sammyEventContext = this;
        lib.overlay();
        $.ajax({
            method: 'DELETE',
            url: viewModel.url + '/' + this.params['id'],
            success: function (data) {
                sammyEventContext.redirect(viewModel.url + '/list#' + viewModel.page());
                viewModel.flashMessages([{type: data.message.type, message: data.message.text}]);
            }
        });
    });
};
Rest.prototype.addRoutes = function(callback) {
    callback(this.sammyApp);//call the custom callback within the scope of sammy js "this"
};

Rest.prototype.handleListData = function(data) {
    lib.showMessages();
    viewModel.action('list');
    viewModel.title(data.title);

    if (data.lists && data.lists.length) {
        viewModel.listData(data.lists);
        viewModel.hasRecords(true);
    } else {
        viewModel.hasRecords(false)
    }
    viewModel.paginator(data.paginator);
    viewModel.form(null);
};

Rest.prototype.handleMoreListData = function(data) {};

Rest.prototype.setHandleMoreListData = function(callback) {
    if(typeof callback != 'function') throw 'Invalid callback provided';
    this.handleMoreListData = callback;
};

Rest.prototype.handleAddEditResponse = function(data, self, removeOverlay){
    if (typeof data.message == 'object') {
        viewModel.flashMessages([{type: data.message.type, message: data.message.text}]);
        if(!data.message.no_redir){
            self.redirect(viewModel.url + '/list#' + viewModel.page());
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
};

Rest.prototype.handleMoreAddEditResponse = function(data){};

Rest.prototype.setHandleMoreAddEditResponse = function(callback){
    if(typeof callback != 'function') throw 'Invalid callback provided';
    this.handleMoreAddEditResponse = callback;
};

var sammyApp = new Sammy();
var rest = new Rest(sammyApp);