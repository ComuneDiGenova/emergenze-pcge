<script type="text/javascript">
    const default_start = new Date();
    $(() => {
        $("#ui_date_start").datepicker({
            format: "dd/mm/yyyy",
            value: default_start,
            defaultViewDate: default_start,
            startDate: default_start,
            todayHighlight: true,
            todayBtn: true,
            clearBtn: true,
            changeYear: true,
            changeMonth: true,
            autoSize: true,
            language: "it",
            orientation: "bottom auto",
            showAnim: "fadeIn",
            showOnFocus: true,
        });
    });
</script>