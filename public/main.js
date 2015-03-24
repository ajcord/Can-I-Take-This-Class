$("#dept-dropdown a").click(function(e) {
    var dept = e.target.innerHTML;
    $("#dept-name").text(dept);
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
                var avail = parsed[i].availability;
                console.log(avail);
                var totalSections = 0;
                var closedSections = 0;
                for (var i = 0; i < avail.length; i++) {
                    totalSections += parseInt(avail[i].num);
                    if (avail[i].status == "0") {
                        closedSections += parseInt(avail[i].num);
                    }
                }
                $(openCell).text(closedSections + " " + totalSections);
                $(row).append([numCell, nameCell, openCell]);
                table.append(row);
            }
        }
    });
});