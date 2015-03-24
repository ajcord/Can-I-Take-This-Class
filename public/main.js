$("#subj-dropdown a").click(function(e) {
    var subj = e.target.innerHTML;
    $("#subj-name").text(subj);
    $.ajax({
        type: "GET",
        url: "retriever.php",
        data: {
            "year": 2015,
            "term": "fall",
            "subject": subj,
        },
        success: function(data) {
            var parsed = $.parseJSON(data);
            console.log(parsed);
            var table = $("#courses-table");
            table.empty();
            for (var i in parsed) {
                var row = document.createElement("tr");
                var numCell = document.createElement("td");
                var nameCell = document.createElement("td");
                var openCell = document.createElement("td");
                $(numCell).text(parsed[i].num);
                $(nameCell).text(parsed[i].name);

                //Calculate the number of open sections vs. total
                var avail = parsed[i].status;
                var totalSections = 0;
                var openSections = 0;
                for (var i in avail) {
                    totalSections += parseInt(avail[i]);
                    if (parseInt(i) > 0) {
                        openSections += parseInt(avail[i]);
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