

(function($) {

    $.extend($.fn, {
	
        msgErrors: function( errorString, append ) {
            append = (append==undefined ?  false : append);
            if(append) {
                this.addClass("ui-state-error")
                .append("<br />" + errorString);
            } 
            else {
                this.msgClean();
                this.addClass("ui-state-error")
                .html(errorString);
            }
            
        },
        
        msgSuccess: function( successString, append ) {
            append = append || false;
            if(append) {
                this.addClass("ui-state-valid")
                .append("<br />" + successString);
            } 
            else {
                this.msgClean();
                this.addClass("ui-state-valid")
                .html(successString);
            }
            
        },
        
        msgClean: function() {
            this.removeClass("ui-state-error").removeClass("ui-state-valid").html("");
        }			
    });


})(jQuery);
