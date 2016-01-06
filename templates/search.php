<form class="form-horizontal" action="historical.php" method="GET">
    <div class="row">
        <div class="col-md-6 col-md-offset-3">
            <h2 class="text-center">Search for a class or subject</h2>
            <div class="input-group col-md-12">
                <input id="search-field" name="q" type="text" class="form-control input-lg" placeholder="e.g. CS 225, PHYS, etc."
<?php if (!is_null($_GET["q"])): ?>
                    value="<?php echo $_GET['q'] ?>"
<?php endif ?>
                />
<?php if (!is_null($_GET["semester"])): ?>
                <input type="hidden" name="semester" value="<?php echo $_GET['semester'] ?>" />
<?php endif ?>
                <span class="input-group-btn">
                    <button id="search-button" class="btn btn-info btn-lg" type="submit">
                        <span class="glyphicon glyphicon-search"></span>
                    </button>
                </span>
            </div>
        </div>
    </div>
</form>