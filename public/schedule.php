<?php include "../templates/header.php"; ?>

<div class = "container">
    <div class = "jumbotron">
        <div class="row">
            <div class="col-md-6">
        		<h2>Search for a course</h2>
                <div id="custom-search-input">
                    <div class="input-group col-md-12">
                        <input type="text" id="classname" class="form-control input-lg" placeholder="CS225" />
                        <span class="input-group-btn">
                            <button class="btn btn-info btn-lg" id="classbutton" type="button">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
    	</div>
    </div>
</div>

<div id="div1">Likelihood for classes.</div>

<?php include "../templates/footer.php"; ?>