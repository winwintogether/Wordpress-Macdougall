(function ($) {
    /* check mobile devices */
    var isMobile = {
        Android: function () {
            return navigator.userAgent.match(/Android/i);
        },
        BlackBerry: function () {
            return navigator.userAgent.match(/BlackBerry/i);
        },
        iOS: function () {
            return navigator.userAgent.match(/iPhone|iPad|iPod/i);
        },
        Opera: function () {
            return navigator.userAgent.match(/Opera Mini/i);
        },
        Windows: function () {
            return navigator.userAgent.match(/IEMobile/i);
        },
        any: function () {
            return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
        }
    };

    /* team-archive-template */
    if ($('.team-archive-template').length > 0) {

        function generateTeamMemberDialog(widget) {

            var checkClickedOverLay = false; // check if overlay is clicked firstly
            widget.find('.team_member-overlay').off('click').on('click', function (e) {
                if (isMobile.any()) {
                    if (!checkClickedOverLay) {
                        e.preventDefault();
                        checkClickedOverLay = true;
                    }
                } else {
                    e.preventDefault();
                    widget.find('.team_member__popup-overlay, .team_member__popup').addClass('active');
                }
            });

            widget.find('.team_member-overlay').off('blur').on('blur', function (e) {
                checkClickedOverLay = false;
            });

            widget.find('.team_member__popup-overlay, .team_member__popup-close').off('click').on('click', function (e) {
                e.preventDefault();
                widget.find('.team_member__popup-overlay, .team_member__popup').removeClass('active');
            });
        }

        $('.team_member-item').each(function () {
            generateTeamMemberDialog($(this));
        });
    }
})(jQuery);