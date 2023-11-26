function redirectToLogin() {
    window.location.href = 'login.html';
}


$(document).ready(function () {
    $("#signupForm").submit(function (e) {
        e.preventDefault();
        var formData = $(this).serialize();

        
        $.ajax({
            type: "POST",
            url: "php/register.php",
            data: formData,
            success: function (response) {
                swal({
                    title: "Signup Completed",
                    text: "Account Created",
                    icon: "success",
                }).then((value) => {
                    
                    window.location.href = "login.html";
                });
            },
            error: function (xhr, status, error) {
                swal({
                    title: "Erro uploading Details",
                    text: "Failed",
                    icon: "error",
                });
            }
        });
    });
});
