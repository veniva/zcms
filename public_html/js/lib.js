var lib = {
    masked: false,
    overlay: function(){
        if(!this.masked){
            var $body = $(document.body);
            $body.showLoadingMask();
            this.masked = true;
        }
    },
    removeOverlay: function(){
        if(this.masked){
            var $body = $(document.body);
            $body.hideLoadingMask();
            this.masked = false;
        }
    },
    get: function(){
        this.overlay();
        var self = this;
        $.get.apply(this, arguments).always(function(){
            self.removeOverlay();
        });
    },
    post: function(){
        this.overlay();
        var self = this;
        $.post.apply(this, arguments).always(function(){
            self.removeOverlay();
        });
    },
    showMessages: function(){
        viewModel.messages(viewModel.flashMessages());
        window.setTimeout(function(){
            $('.alert').alert('close').on('closed.bs.alert', function () {
                viewModel.messages([]);
                viewModel.flashMessages([]);
            });
        }, 3000);
    }
};