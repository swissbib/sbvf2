function registerExtendedBulkActions() {
    $('form[name="bulkActionForm"] input[type="button"][name="print"]').each(function () {
        $(this).unbind('click').click(function () {
            $('form[name="bulkActionForm"] input.checkbox_ui').each(function () {
                if ($(this).prop('checked')) {
                    $(this).closest("tr").removeClass("no-print");
                } else {
                    $(this).closest("tr").addClass("no-print");
                }
            });
            window.print();
        });
    });
}

$(document).ready(function () {
    registerExtendedBulkActions();
});