<script type="text/javascript">
    const default_start = new Date();
    $(() => {

        const dp = $("#ui_date_start").datepicker({
            format: "yyyy-mm-dd",
            // value: default_start,
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
        }).on('changeDate', function(evt) {
            get_start_event();
        });
        dp.datepicker('setDate', default_start);

        function get_start_event() {
            const start_event_date = dp[0].value;
            const start_event_hours = document.getElementById("ui_hh_start").value;
            const start_event_minutes = document.getElementById("ui_mm_start").value;
            document.getElementById("data_ora_inizio").value = `${start_event_date} ${start_event_hours}:${start_event_minutes}`
        };

        document.getElementById("ui_hh_start").addEventListener("change", (event) => {
            get_start_event();
        });

        document.getElementById("ui_mm_start").addEventListener("change", (event) => {
            get_start_event();
        });

    });

</script>