(function() {
    var update = function() {
        var $this = $(this).parent();
        var $enable_checkbox = $this.find('input[type=checkbox][id$=_enable]');
        var $configuration_fields = $enable_checkbox.parent().parent().parent().siblings();
        var $legend = $this.find('legend');
        $legend.find('.fa-fw').remove();
        if($enable_checkbox.prop('checked')) {
            $legend.prepend('<i class="fa fa-check-square-o fa-fw"></i>');
            $configuration_fields.show();
        } else {
            $legend.prepend('<i class="fa fa-square-o fa-fw"></i>');
            $configuration_fields.hide();
        }
    };
    $('input[type=checkbox][id$=_enable]')
        .parent().parent().parent().hide()
        .parents('fieldset')
        .find('legend')
        .on('click', function() {
            var $enable_checkbox = $(this).parent().find('input[type=checkbox][id$=_enable]');
            $enable_checkbox.prop('checked', !$enable_checkbox.prop('checked'));
        })
        .on('click', update)
        .each(update);
    $('.plugin-doc-page').on('click', function(ev) {
        ev.stopPropagation();
    });
})();
