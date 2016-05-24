//enables the parsing of knockout bindings inserted with the new HTML
ko.bindingHandlers['dynamicHtml'] = {
    'update': function (element, valueAccessor, allBindings, viewModel, bindingContext) {
        // setHtml will unwrap the value if needed
        ko.utils.setHtml(element, valueAccessor());
        ko.applyBindingsToDescendants(bindingContext, element);
    }
};

//prevents the execution of the parent event when clicked onto a child.
ko.bindingHandlers.stopBubble = {
    init: function(element) {
        ko.utils.registerEventHandler(element, "click", function(event) {
            event.cancelBubble = true;
            if (event.stopPropagation) {
                event.stopPropagation();
            }
        });
    }
};