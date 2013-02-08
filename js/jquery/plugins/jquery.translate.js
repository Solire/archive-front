
(function($) { // Hide scope, no $ conflict


$.localise = function(key) {
        //Decommenter pour la recette, (affichera les textes pas traduit dans la console)
//        main[key] == null ? console.log(key) : "";
	return main[key] == null || main[key] == "" ? key : main[key];
};

// Localise it!
$._ = $.localise;





})(jQuery);
