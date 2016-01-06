<?php include "../templates/header.php"; ?>

<div class="container">
    <div class="jumbotron text-center">
        <h1>Can I Take This Class?</h1>

        <p>
            Find out whether you'll get into the classes you want.
            <a href="about.php">Learn More</a>
        </p>
        
        <form class="form-horizontal" action="historical.php" method="GET">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <h2>Search for a class or subject</h2>
                    <div class="input-group col-md-12">
                        <input id="search-field" name="q" type="text" class="form-control input-lg" placeholder="e.g. CS 225, PHYS, etc." />
                        <span class="input-group-btn">
                            <button id="search-button" class="btn btn-info btn-lg" type="submit">
                                <span class="glyphicon glyphicon-search"></span>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include "../templates/footer.php"; ?>