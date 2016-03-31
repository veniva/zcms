rest.getList();
rest.getEdit();
rest.getAdd();
rest.putEdit();
rest.postAdd();
rest.del();

sammyApp.bind('run-route', function() {
    $('.alert').alert('close').on('closed.bs.alert', function () {
        viewModel.messages([]);
        viewModel.flashMessages([]);
    });
});
sammyApp.run(viewModel.url+'/list');