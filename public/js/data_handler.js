
    $(document).ready(function() {
		var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
		var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
        $("#data-table").DataTable({
            "paging": true,
            "pageLength": 20,
            "searching": true,
            "ordering": true,
            "info": true,
            dom: 'Bfrtip', // Enables buttons
            buttons: [
                {
                    extend: 'csvHtml5',
                    text: 'Export CSV',
                    className: 'btn btn-primary'
                },
                {
                    extend: 'excelHtml5',
                    text: 'Export Excel',
                    className: 'btn btn-success'
                }
            ]
        });
    });

 $(document).on("click", ".verify-button", function() {
    var csrfName = "<?= $this->security->get_csrf_token_name(); ?>";
    var csrfHash = "<?= $this->security->get_csrf_hash(); ?>";
    var rowId = $(this).data("id");
    var table = $(this).data("table");
    var button = $(this);

    $.post("<?php echo base_url()?>data/verify_status/" + table, {
        id: rowId,
        [csrfName]: csrfHash // Include CSRF token
    }, function(response) {
        if (response.success) {
            button.toggleClass("btn-danger btn-success").text(response.status);
            Swal.fire({
                icon: "success",
                title: "Success",
                text: "Verification status updated successfully!",
                timer: 2000,
                showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Verification update failed. Please try again.",
            });
        }
    }, "json").fail(function() {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Server error. Please check your network or try again later.",
        });
    });
});
$(document).on("blur", "td[contenteditable=true]", function() {
    var csrfName = "<?= $this->security->get_csrf_token_name(); ?>";
    var csrfHash = "<?= $this->security->get_csrf_hash(); ?>";
    var rowId = $(this).closest("tr").data("id");
    var table = $(this).closest("tr").data("table");
    var field = $(this).data("field");
    var value = $(this).text();

    $.post("<?php echo base_url()?>data/update_field/" + table, {
        id: rowId,
        field: field,
        value: value,
        [csrfName]: csrfHash // Include CSRF token
    }, function(response) {
        if (response.success) {
            $.notify("Data Updated", { className: "success", position: "top right" });
        } else {
            $.notify("Update failed: " + response.message, { className: "error", position: "top right" });
        }
    }, "json").fail(function() {
        $.notify("Server error. Please try again later.", { className: "error", position: "top right" });
    });
});


$(document).ready(function () {
    $("#tables_data").on("submit", function (event) {
        event.preventDefault(); // Prevent default form submission (Page reload)

        var form = $(this);
        var formData = form.serialize(); // Serialize form data

        // Include CSRF Token
        formData += "&" + $("input[name='<?= $this->security->get_csrf_token_name(); ?>']").serialize();

        $.ajax({
            url: form.attr("action"), // Gets the action URL dynamically
            type: "POST",
            data: formData,
            dataType: "json",
            beforeSend: function () {
                $("button[type='submit']").prop("disabled", true); // Disable button to prevent multiple submissions
            },
            success: function (response) {
                if (response.success) {
                    $.notify("Form Data Saved successfully!", { className: "success", position: "top right" });

                    // Optionally, reset the form after submission
                    $("#tables_data")[0].reset();
                } else {
                    $.notify("Error: " + response.message, { className: "error", position: "top right" });
                }
            },
            error: function () {
                $.notify("Server error. Please try again later.", { className: "error", position: "top right" });
            },
            complete: function () {
                $("button[type='submit']").prop("disabled", false); // Re-enable button
            }
        });
    });
});

