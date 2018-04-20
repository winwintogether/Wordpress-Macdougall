/**
    Responsive Menu JS file.
    NOT Safe to Copy

    Do Not Copy
**/

jQuery(function($) {

    /* --> Colour Select Options */
        $.minicolors.defaults = $.extend(
            $.minicolors.defaults, {
                format: 'rgb',
                opacity: true,
                theme: 'bootstrap'
            }
        );
        $('.mini-colours').minicolors();
    /* <-- End Colour Select Options */

    /* --> Preview Options */
        $(document).on('click', '#responsive-menu-preview', function(e) {
            e.preventDefault();
            var form = $('#responsive-menu-pro-form');
            form.attr('action', WP_HOME_URL + '?responsive-menu-pro-preview=true');
            form.attr('target', '_blank');
            form.submit();
            form.attr('action', '');
            form.attr('target', '');
        });
    /* <-- End Preview Options */

    /* --> Font Icons */
        $(document).on('click', '.delete-font-icon-row', function() {
            $(this).closest('tr').remove();
        });

        $(document).on('click', '#add-font-icon', function() {
            var lastRow = $('#font-icon-container tr').last();
            var nextRow = lastRow.clone();
            nextRow.find(':text').val('');
            lastRow.after(nextRow);
            /* Solution: https://github.com/silviomoreto/bootstrap-select/issues/605#issuecomment-186148737 */
            nextRow.find('.bootstrap-select').replaceWith(function() { return $('select', this); });
            nextRow.find('select').selectpicker();
        });
    /* <-- End Font Iconts */

    /* --> Header Bar Scripts */
        $('#header-bar-sortable').sortable({
            revert: true,
            placeholder: 'header-dashed-placeholder'
        });
    /* <-- End Header Bar Scripts */

    /* --> Desktop Menu Scripts */
        $('.responsive-menu-desktop-menu-label').on('click', function(e) {
            $('.responsive-menu-desktop-menu-options-container').hide();
            $('.responsive-menu-desktop-menu-widget-container').hide();
            $('#responsive-menu-desktop-menu-option-' + $(this).data('id')).show();
            $('#responsive-menu-desktop-menu-widget-' + $(this).data('id')).show();
            $('.responsive-menu-desktop-menu-label').removeClass('responsive-menu-desktop-menu-label-active');
            $(this).addClass('responsive-menu-desktop-menu-label-active');
        });

        $(document).on('click', '.responsive-menu-desktop-menu-options-container .responsive-menu-desktop-menu-widget .responsive-menu-desktop-menu-widget-header', function(e) {
            $(this).siblings('.responsive-menu-desktop-menu-widget-body').slideToggle(100);
        });

        $(document).on('click', '.responsive-menu-desktop-menu-widget .responsive-menu-desktop-menu-widget-header .glyphicon', function() {
            $(this).closest('.responsive-menu-desktop-menu-widget').remove();
        });

        $(document).on('changed.bs.select', '.desktop-menu-type-switcher', function(event, clickedIndex) {
            $mega_menu_background =
                $(this)
                    .parents('.responsive-menu-desktop-menu-option-container')
                    .siblings('.responsive-menu-desktop-menu-mega-menu-option');

            if(clickedIndex == 1) {
                $mega_menu_background.removeClass('hidden');
            } else {
                $mega_menu_background.addClass('hidden');
            }
        });
    /* <-- End Desktop Menu Scripts */

});