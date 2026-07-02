$(document).ready(function () {
    $('#login').click(function () {
        var user = $('#user').val();
        var pass = $('#pass').val();
        if ($.trim(user).length > 0 && $.trim(pass).length > 0) {
            $.ajax({
                url: "../capa_logica/logueame.php",
                method: "POST",
                data: {user: user, pass: pass},
                cache: "false",
                beforeSend: function () {
                    $('#login').val("Conectando...");
                },
                success: function (data) {
                    $('#login').val("Login");

                    if (data == 1) {
                        $(location).attr('href', 'principal.php');

                    } else {
                        $("#result").html("<br><div class='alert alert-dismissible alert-danger'><button type='button' class='close' data-dismiss='alert'>&times;</button><strong>¡Error!</strong> los datos ingresados son incorrectas.</div>");
                    }
                }
            });
        } else {
            alert("¡Por favor ingresar datos!");
        }
        ;
    });
});