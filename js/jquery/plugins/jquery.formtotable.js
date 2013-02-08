//My plugins
(function($) {
    
    var methods = {
    
            
        send : function () {
            var options = this.options;
            var form = this
            var dataForm =  $(form).serialize();
            var dataForm2 = new Array();
            var idExplode = $(form).attr('id').split('-');
            dataForm2.push('tabletoinsert='+idExplode[0]);
            $('.fg-buttonset-single', form).each(function () {
                dataForm2.push($(this).attr('name')  + '=' +  $('.fg-button.ui-state-active', this).attr('name') );
            })
                
            if(dataForm2.length > 0)
                dataForm = dataForm + '&' + dataForm2.join('&');
            
            if (typeof options.beforeSubmit == 'function') { // make sure the callback is a function
                if(options.beforeSubmit.call(form, dataForm) === false) // brings the scope to the callback
                    return false;
            }
            
            $.post('tools/form-save.html', dataForm, function (data) {
                if (typeof options.callback == 'function') { // make sure the callback is a function
                    options.callback.call(form, data); // brings the scope to the callback
                }

                
            }, 'json')
        }
            

    };
    $.fn.formToTable = function(options) {
        // extend the options from pre-defined values:
            
        var defaults = {
            callback: function() {},
            beforeSubmit: function() {}
        };
            
           
        function submitEvent() {
            
            var $this = this
            
            
            $('input[type!=hidden], select', this).keypress(function (e) {
                if ((e.which && e.which == 13) || (e.keyCode && e.keyCode == 13)) {
                    e.preventDefault();
                    return methods[ 'send' ].apply( $this);
                    return false;
                } else {
                    return true;
                }
            });
            
            
            
            
            $this.options = options;
            $('button[type=submit]', this).bind("click", function (event) {
                event.preventDefault();
                return methods[ 'send' ].apply( $this);
            })
        }
        if ( methods[ options ] ) {
            $(this).each(function() {
                methods[ options ].apply( this);
            });
                
        } else {
            // Extend our default options with those provided.
            var options = $.extend(defaults, options);
            $(this).each(submitEvent);   
        }

        // interface fluide
        return $(this);
            
    }
})(jQuery);