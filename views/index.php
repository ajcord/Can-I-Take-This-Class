<?php include "../templates/header.php" ?>

<div class="jumbotron text-center" id="main-jumbotron">

    <? if ($_GET["status"] == "invalid_course"): ?>
        <div class="container">
            <div class="alert alert-danger">
                Invalid course. Enter a course in one of the following formats:
                SUBJ 123, SUBJ123, subj 123, subj123.
            </div>
        </div>
    <? elseif ($_GET["status"] == "invalid_date"): ?>
        <div class="container">
            <div class="alert alert-danger">
                Invalid date. Enter a date that occurs on or after
                April 6, 2015.
            </div>
        </div>
    <? elseif ($_GET["status"] == "course_does_not_exist"): ?>
        <div class="container">
            <div class="alert alert-danger">
                The selected course does not exist in any semester on record.
            </div>
        </div>
    <? endif ?>

    <h1>Can I Take This Class?</h1>

    <p>
        Predict your chances of getting the classes you want at UIUC.
    </p>
    
    <form class="form-horizontal" action="/prediction" method="GET">
        <? if ($_GET["status"] == "invalid_course" ||
                $_GET["status"] == "course_does_not_exist"): ?>
            <div class="form-group form-group-lg has-error">
        <? else: ?>
            <div class="form-group form-group-lg">
        <? endif ?>
            <label for="course" class="col-sm-2 col-sm-offset-2 control-label">Class</label>
            <div class="col-sm-4">
                <input type="text" id="course" name="course" class="form-control" placeholder="Enter a class"
                <? if (isset($_GET["course"])): ?>
                    value="<?= htmlspecialchars($_GET["course"]) ?>"
                <? endif ?>
                />
            </div>
        </div>
        <? if ($_GET["status"] == "invalid_date"): ?>
            <div class="form-group form-group-lg has-error">
        <? else: ?>
            <div class="form-group form-group-lg">
        <? endif ?>
            <label for="date" class="col-sm-2 col-sm-offset-2 control-label">Registration date</label>
            <div class="col-sm-4">
                <input type="date" id="date" class="form-control" name="date" placeholder="Enter your registration date" min="2015-04-06"
                <? if (isset($_GET["date"])): ?>
                    value="<?= htmlspecialchars($_GET["date"]) ?>"
                <? endif ?>
                />
            </div>
        </div>
        <div class="form-group form-group-lg">
            <div class="col-sm-offset-4 col-sm-4">
                <button type="submit" id="search-button" class="btn btn-info btn-lg">Predict</button>
            </div>
        </div>
    </form>
</div>

<div class="container">
    <h2>About</h2>
    <p>
        <b>Can I Take This Class</b> analyzes registration data
        to predict whether you'll get the classes you want.
        Kind of like a Magic 8 Ball, except it's smart and
        you don't need to shake it.
        Works at
        <a href="http://illinois.edu">every four year university
            in Champaign-Urbana, IL</a>.
    </p>
    <p>
        It was developed by
        <a href="http://alexcordonnier.com">Alex Cordonnier</a>
        as the successor to ClassMaster, a CS 411 final project
        developed with Clarence Elliott, Gaurang Jain, and Sean Mulroe.
    </p>

    <h2>For developers</h2>
    <p>
        Want to know more?
        The source code is available on
        <a href="https://github.com/ajcord/Can-I-Take-This-Class">GitHub</a>.
        Check out the
        <a href="https://github.com/ajcord/Can-I-Take-This-Class/wiki/API-Docs">API</a>!
    </p>
</div>

<?php include "../templates/footer.php" ?>