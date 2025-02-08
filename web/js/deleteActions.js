(function ($) {
    $(function () {
        function setupDeleteButton(entity) {
            $(document).on("click", `.delete-${entity}`, function (e) {
                e.preventDefault();
                var id = $(this).attr("data-id");
                $(`#delete-btn-${entity}-id`).attr('data-id', id);
                $(`#delete${entity.charAt(0).toUpperCase() + entity.slice(1)}`).modal('show');
            });

            $(document).on("click", `#delete-btn-${entity}-id`, function (e) {
                e.preventDefault();
                var id = $(this).attr('data-id');
                var $this = $(this);

                $.ajax({
                    url: `index.php?controller=${entity.charAt(0).toUpperCase() + entity.slice(1)}&action=delete`,
                    type: 'POST',
                    data: { id: id },
                    success: function (res) {
                        $(`#container-${entity}-id`).html(res);
                        $this.attr('data-id', '');
                        $(`#delete${entity.charAt(0).toUpperCase() + entity.slice(1)}`).modal('hide');
                    }
                });
            });
        }

        ['curier', 'product', 'user', 'order'].forEach(entity => setupDeleteButton(entity));
    });
}(jQuery));
