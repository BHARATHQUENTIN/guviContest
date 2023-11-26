$(document).ready(function () {

    function fetchUserData() {
        $.ajax({
            type: "GET",
            url: "php/profile.php",
            dataType: "json",
            success: function (response) {
                console.log("AJAX Success:", response);

                if (response.hasOwnProperty('error')) {
                    console.log("Error: " + response.error);
                } else if (response.userData) {
                    var userData = response.userData;

                    console.log("UserData:", userData);

                    // Update the frontend
                    $("#firstName").val(userData.firstname);
                    $("#email").val(userData.email);
                    
                    $("#contact1").val(userData.contact);
                    $("#age1").val(userData.age);
                    $("#dateOfBirth1").val(userData.dateOfBirth);
                    $("#address1").val(userData.address);

                    localStorage.setItem('userData', JSON.stringify(userData));
                } else {
                    console.error("Unexpected response format or missing properties");
                }
            },

            error: function (xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText);
            }
        });
    }

    function updateUserData(formData) {
        $.ajax({
            type: "POST",
            url: "php/profile.php",
            data: formData + '&action=update',
            dataType: "json",
            success: function (response) {
                console.log("AJAX Success:", response);
                if (response.success) {
                    $('#editDetailsModal').modal('hide');
                    // Instead of location.reload(), call fetchUserData
                    fetchUserData();
                    // Notify user about the successful update
                    swal({
                        title: "Updated",
                        text: "Your Profile Updated Successfully!",
                        icon: "success",
                        button: "ok",
                      });
                } else {
                    swal({
                        title: "Error",
                        text: response.error,
                        icon: "error",
                        button: "ok",
                      });
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText);
            }
        });
    }

    function insertOrUpdateUserData(formData) {
        $.ajax({
            type: "POST",
            url: "php/profile.php",
            data: formData + '&action=insertOrUpdate',
            dataType: "json",
            success: function (response) {
                console.log("AJAX Success:", response);
                if (response.success) {
                    var actionMessage = response.updated ? "updated" : "inserted";
                    $.notify("Profile data " + actionMessage + " successfully", "success");
                } else {
                    console.error("Insert/Update failed:", response.error);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText);
            }
        });
    }
    
    fetchUserData();
    
    $("#profileForm").submit(function (e) {
        e.preventDefault();
        var formData = $(this).serialize();
        console.log("Form Data:", formData);
    
       
        var submissionCount = formData.split("&").length; 
    
        if (submissionCount > 4) {
            
            insertOrUpdateUserData(formData);
        } else {
           
            updateUserData(formData);
        }
    });
});
$(document).ready(function () {
    

    
    $("#logoutBtn").click(function (e) {
        e.preventDefault();

       
        $.ajax({
            type: "POST",
            url: "php/profile.php",
            data: { logout: true }, 
            dataType: "json",
            success: function (response) {
                console.log("Logout Success:", response);
                if (response.success) {
                 
                    window.location.replace("login.html");
                } else {
                    console.error("Logout failed:", response.error);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX Error:", xhr.responseText);
            }
        });
    });

 
});




