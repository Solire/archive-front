//var sort_elmt = $(null);
//var sort_box = $(null);
var positions = {};

$(function(){
    var confirmationSupprMessage = '<p style="color: red;">'
    confirmationSupprMessage += '<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>Attention ! Cette action supprimera la page dans tous les langues.'
    confirmationSupprMessage += '</p>'
    confirmationSupprMessage += '<div style="margin-left: 23px;margin-top: 16px;">Etes-vous sur de vouloir supprimer cette page ?</div>'
    
    //// SUPPRIMER UNE PAGE.
    confirm = $('<div>')
    .html(confirmationSupprMessage)
    .dialog({
        open: function(){
            $('.ui-widget-overlay').hide().fadeIn();
            if(!$('.ui-dialog-buttonset button').hasClass("btn"))
                $('.ui-dialog-buttonset button').attr("class", "").addClass("btn gradient-blue").unbind('mouseout keyup mouseup hover mouseenter mouseover focusin focusout mousedown focus').wrapInner("<a></a>");
        },
        beforeClose: function(){
            $('.ui-widget-overlay').remove();
            $("<div />", {
                'class':'ui-widget-overlay'
            }).css({
                height: $(document).height(),
                width: $(document).width(),
                zIndex: 1001
            }).appendTo("body").fadeOut(function(){
                $(this).remove();
            });
        },

        modal: true,
        width : "446px",
        autoOpen : false,
        resizable: false,
        title : "Confirmation de suppression de page",
        show: {
            effect:   "fade",
            duration: 1000
        },
        hide: {
            effect:   "fade",
            duration: 500
        },

        buttons: {
            "Ok" : function(){
                $(this).dialog("close");
            },
            "Annuler" : function(){
                $(this).dialog("close");
            }
        }
    });
    
    var confirmOpen = function(sort_elmt) {
        var sort_box = sort_elmt.parent();
        var id_gab_page = parseInt(sort_elmt.attr('id').split('_').pop());
        
        confirm.dialog('option', 'buttons', {
            "Ok" : function(){
                $.post(
                    'page/delete.html',
                    {
                        id_gab_page : id_gab_page
                    },
                    function(data){
                        if(data.status == 'success')
                            sort_elmt.slideUp('fast', function(){
                                $(this).remove();
                                sort_box.sortable('refresh');
                                confirm.dialog("close")
                                $.sticky("La page a été supprimée", {
                                    type:"success"
                                });
                            })
                    },
                    'json'
                    );
            },
            "Annuler" : function(){
                $(this).dialog("close");
            }            
        }).dialog('open');
        $(".supprimer", sort_elmt).effect("transfer", {
            to: confirm.dialog("widget"),
            className: "ui-effects-transfer"
        }, 500);

    }
	
    $('.supprimer').live('click', function(){
        confirmOpen($(this).parents('.sort-elmt').first());

        return false
    });
	
    //// RENDRE VISIBLE UNE PAGE.
    $('.rendrevisible').live('click', function(){
        var $this = $(this);
        var id_gab_page = parseInt($this.parents('.sort-elmt').first().attr('id').split('_').pop());
        var checked = $this.is(':checked');
		
        $.post(
            'page/visible.html',
            {
                id_gab_page : id_gab_page,
                visible     : checked ? 1 : 0
            },
            function(data){                
                if(data.status != 'success') {
                    $this.attr('checked', !checked);
                    $.sticky("Une erreur est survenue", {
                        type:"error"
                    });
                } else {
                    if(checked) {
                        $.sticky("La page a été rendue visible", {
                            type:"success"
                        });
                    } else {
                        $.sticky("La page a été rendue invisible", {
                            type:"success"
                        });
                    }
                }
                
                
                
                
            },
            'json'
            );
    });

    //// GESTION DU TRI DES PAGES.
    var initTri = function () {
        $('.sort-box').each(function(){
            var i = 1;
            $(this).children().each(function(){
                positions[parseInt($(this).attr('id').split('_').pop())] = i++;
            });

            $(this).sortable({
                placeholder: 'empty',
                items: '> .sort-elmt',
                handle: '.sort-move',
                deactivate: function(){
                    var i = 1;
                    $(this).children().each(function(){
                        positions[parseInt($(this).attr('id').split('_').pop())] = i++;
                    });
                    orderProcess();
                }
            });
        });
    }
        
    var orderProcess = function(){
        $.post('page/order.html', {
            'positions' : positions
        }, function(data){
            if(data == "Succès") {
                $.sticky("Succès du déplacement", {
                    type:"success"
                });
            } else {
                $.sticky("Une erreur est survenue.", {
                    type:"error"
                });
            }
            
        });

        return false;
    }

    initTri();

	
    $('select[name=id_sous_rubrique]').change(function(){
        var id_sous_rubrique = $(this).val();
        $.cookie('id_sous_rubrique', id_sous_rubrique, {
            path : '/'
        });
        $(this).parents('form').submit();
    });

    //// OUVERTURE / FERMETURE DES PAGES PARENTES.
    $('legend').live('click', function(){
        var $legend = $(this)
        if ($(this).next('div').is(':hidden') && $(this).next('div').html()=='') {
                
            $legend.find('span.ui-icon-plus').addClass("ui-icon-moins")
            if (!$(this).next('div').hasClass('children-loaded')) {         
                var id = $(this).parent().attr('id').split('_').pop();
                $(this).next('div').load('page/children.html', {
                    id_parent : id
                }, function(data){
                    
                    $(this).addClass('children-loaded');
                    if (data != '') {
                        initTri();
                        $(this).slideToggle(500);
                        $(this).siblings('.cat-modif').slideToggle(500);
                    }
                });
            }
        }
        else {
            $legend.find('span.ui-icon-plus').toggleClass("ui-icon-moins")
            $(this).next('div').slideToggle(500);
            $(this).siblings('.cat-modif').slideToggle(500);
        }
        
        return false;
    });
    
    
    $(".sort-move").live("click", function(e) {
        e.preventDefault()
    })
    
});