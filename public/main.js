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
                $(numCell).text(i);
                $(nameCell).text(parsed[i]);
                $(row).append([numCell, nameCell]);
                table.append(row);
            }
        }
    });
});