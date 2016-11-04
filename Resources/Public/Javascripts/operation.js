(function() {
    "use strict";

    function operationInit() {
        if (typeof jQuery === 'undefined') {
            setTimeout(function() {
                operationInit();
            }, 200);
            return;
        }
        var $ = jQuery;

        var currentSecNavEl = $('#sub-nav .current');
        if (currentSecNavEl.find('ul').length === 0) {
            currentSecNavEl.append('<ul>');
        }
        currentSecNavEl.find('ul').append($('#year-nav li').clone());

        $('button.deleteOperation').click(function() {
            var self = $(this);

            if (!self.hasClass('confirm')) {
                self.addClass('confirm');
                return false;
            }
        });

        $('body').click(function() {
            $('button.deleteOperation.confirm').removeClass('confirm');
        });
    }

    operationInit();
}());
