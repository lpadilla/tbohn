/**
 * JS File - SECCION DE MANEJO DEL MODAL Y LOS BOTONES ASOCIADOS
 */

    (function ($)
    {
        Drupal.behaviors.UserListBo =
            {
                attach: function (context)
                {
                    $('.modal').modal(
                        {
                            dismissible: true, // Modal can be dismissed by clicking outside of the modal
                            opacity: .5, // Opacity of modal background
                            inDuration: 300, // Transition in duration
                            outDuration: 200, // Transition out duration
                            startingTop: '4%', // Starting top style attribute
                            endingTop: '10%' // Ending top style attribute
                        }
                    );
                    $('#user-list-bo-submitBtn').click(function()
                    {
                        var nombre = $(this).attr("valnombre");
                        var email = $(this).attr("valemail");                        
                        $.ajax({
                            url: '/tbo-account-bo-desactivar-usuario',
                            method: "POST",
                            data: { email : email, nombre : nombre }, // Set the number of Li items requested
                            dataType: "json",          // Type of the content we're expecting in the response
                            success: function(data)
                            {
                                window.location.reload();
                            },
                            error: function (xmlhttp)
                            {
                                window.location.reload();
                            }
                        });
                    });
                }
            }
    })(jQuery);

    function cerrar_alerta()
    {
        jQuery('.messages').hide();
    }

    function cargar_datos_modal(item)
    {
        var nombre = jQuery(item).attr("valornombre");
        var email = jQuery(item).attr("valoremail");
        jQuery('#nombreUsuario').text(nombre);
        jQuery("#user-list-bo-submitBtn").attr("valnombre", nombre);
        jQuery("#user-list-bo-submitBtn").attr("valemail", email);
    }
