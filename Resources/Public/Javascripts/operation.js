(function() {
    "use strict";

    function showNextOperations(dom) {
        var nav = dom.find('.page-navigation');
        var nextUrl = nav.find('.next a').attr('href');

        if (nextUrl) {
            var more = $('<a>');
            more.addClass('moreOperations');
            more.append('<span>Mehr</span>');
            more.click(function() {
                $(this).remove();

                $.ajax({
                    url: nextUrl,
                    success: function(data) {
                        data = $(data);

                        $('.operation').last().after(data.find('.operation'));
                        $('.operation').last().after(data.find('.page-navigation'));

                        fitGallery($('.gallery'));

                        showNextOperations($('body'));
                    }
                });
            });
            nav.after(more);
        }

        nav.remove();
    }

    function operationInit() {
        if (typeof jQuery === 'undefined') {
            setTimeout(function() {
                operationInit();
            }, 200);
            return;
        }
        var $ = jQuery;

        showNextOperations($('body'));

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
