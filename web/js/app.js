(function ($) {
    $(function () {

        $('#courier-table-id').dataTable({
            order: [[1, 'asc']],
            columnDefs: [
                {orderable: false, targets: [ 0, -1 ]}
            ]
        });
        
        $('#product-table-id').dataTable({
            order: [[1, 'asc']],
            columnDefs: [
                {orderable: false, targets: [ 0, -1 ]}
            ]
        });

        $('#processedAt').datepicker({ });

        $(document).delegate("#imageFile", "change", function (e) {
            e.preventDefault();

            let formData = new FormData();
            let fileInput = $('#imageFile')[0].files[0];
            if (!fileInput) {
                alert("Please select an image to upload.");
                return;
            }

            var $id = $("#input-id").val();

            formData.append('file', fileInput);
            formData.append('id', $id);

            $.ajax({
                url: 'index.php?controller=Gallery&action=upload', // Server endpoint (PHP file)
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {

                    $('#image-container-id').html(response);
                },
                error: function (response) {
                    $('#uploadStatus').html('<p>Error uploading image.</p>');
                }
            });
        }).delegate('.btn-delete-img', 'click', function (e) {
            e.preventDefault();

            var $id = $(this).attr('data-id');
            var $this = $(this);

            $.ajax({
                url: 'index.php?controller=Gallery&action=deleteImage', // Server endpoint (PHP file)
                type: 'POST',
                data: {
                    id: $id
                },
                success: function (res) {
                    $this.parent().parent().remove();
                }
            });
        }).delegate(".delete-curier", "click", function (e) {
            e.preventDefault();

            var $id = $(this).attr('data-id');

            $("#delete-btn-curier-id").attr('data-id', $id);

            $('#deleteCurier').modal('show');
        }).delegate("#delete-btn-curier-id", 'click', function (e) {
            e.preventDefault();

            var $id = $(this).attr('data-id');
            var $this = $(this);

            $.ajax({
                url: 'index.php?controller=Courier&action=delete', // Server endpoint (PHP file)
                type: 'POST',
                data: {
                    id: $id
                },
                success: function (res) {
                    $('#container-curier-id').html(res);

                    $this.attr('data-id', '');

                    $('#deleteCurier').modal('hide');
                }
            });
        }).delegate(".delete-product", "click", function (e) {
            e.preventDefault();

            var $id = $(this).attr("data-id");

            $("#delete-btn-product-id").attr('data-id', $id);

            $('#deleteProduct').modal('show');
        }).delegate("#delete-btn-product-id", 'click', function (e) {
            e.preventDefault();

            var $id = $(this).attr('data-id');
            var $this = $(this);

            $.ajax({
                url: 'index.php?controller=Product&action=delete', // Server endpoint (PHP file)
                type: 'POST',
                data: {
                    id: $id
                },
                success: function (res) {
                    $('#container-product-id').html(res);

                    $this.attr('data-id', '');

                    $('#deleteProduct').modal('hide');
                }
            });
        });
    });
}(jQuery));