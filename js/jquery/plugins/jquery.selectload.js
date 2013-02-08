//My plugins
(function($) {
    
    $.fn.selectLoad = function () {
        function load() {
            var select = this
            var id = $(select).attr("id");
            var idExplode = id.split('-');
            var srcName = idExplode[0];
            $.post('gabarit/select-load.html', {
                load : srcName
            }, function (data) {
                $(select).empty();
                if($(select).hasClass("ui-select-fac"))
                    $(select).append('<option value="0">&nbsp;</option>');
                $.each(data, function (key, item) {
                    $(select).append('<option value="' + key + '">' + item.name + '</option>');
                })
                //                    if(data.length > 0 ) {
                $(select).selectmenu('destroy');
                $(select).selectmenu({
                    width : 250,
                    style : "dropdown",
                    maxHeight: 200
                });
            //                    }
                    
                
            }, 'json');
        }
        $(this).each(load);   

        // interface fluide
        return $(this);
    }
        
    
})(jQuery);