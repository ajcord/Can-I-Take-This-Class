$("#subj-dropdown a").click(function(e) {
    var dept = e.target.innerHTML;
    $("#subj-name").text(dept);
    $.ajax({
        type: "GET",
        url: "retriever.php",
        data: {
            "year": 2015,
            "sem": "fall",
            "dept": dept,
        },
        success: function(data) {
            var parsed = $.parseJSON(data);
            // console.log(parsed);
            var table = $("#courses-table");
            table.empty();
            for (var i in parsed) {
                var row = document.createElement("tr");
                var numCell = document.createElement("td");
                var nameCell = document.createElement("td");
                var openCell = document.createElement("td");
                $(numCell).text(i);
                $(nameCell).text(parsed[i].name);

                //Calculate the number of open sections vs. total
                var avail = parsed[i].availability;
                var totalSections = 0;
                var openSections = 0;
                for (var i = 0; i < avail.length; i++) {
                    totalSections += parseInt(avail[i].num);
                    if (parseInt(avail[i].status) > 0) {
                        openSections += parseInt(avail[i].num);
                    }
                }
                if (totalSections) {
                    if (openSections == 0) {
                        $(openCell).text("Availability unknown");
                    } else {
                        var percentOpen = openSections / totalSections;
                        $(openCell).text(Math.round(percentOpen * 100) + "%");
                    }
                } else {
                    $(openCell).text("Schedule not available");
                }
                $(row).append([numCell, nameCell, openCell]);
                table.append(row);
            }
        }
    });
});