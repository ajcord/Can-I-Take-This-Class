<?php include "../templates/header.php" ?>

<? if (isset($_GET["status"])):
    $status = $_GET["status"] ?>

    <? if ($status == "invalid_course"): ?>
        <div class="alert alert-danger">
            Invalid course. Enter a course in one of the following formats:
            SUBJ 123, subj 123, SUBJ123, subj123.
        </div>
    <? elseif ($status == "invalid_date"): ?>
        <div class="alert alert-danger">
            Invalid date. Enter a date in the format YYYY-MM-DD that occurs
            after April 6, 2015.
        </div>
    <? elseif ($status == "course_does_not_exist"): ?>
        <div class="alert alert-danger">
            The selected course does not exist in any semester on record.
        </div>
    <? endif ?>

<? endif ?>

<div class="jumbotron text-center">
    <h1>Can I Take This Class?</h1>

    <p>
        Find out whether you'll get into the classes you want at UIUC.
        <a href="about.php">Learn More</a>
    </p>
    
    <form class="form-horizontal" action="/prediction/" method="GET">
        <div class="form-group form-group-lg">
            <label for="course" class="col-sm-2 col-sm-offset-2 control-label">Class</label>
            <div class="col-sm-4">
                <input type="text" id="course" name="course" class="form-control" placeholder="Enter a class"
                <? if (isset($_GET["course"])): ?>
                    value="<?= htmlspecialchars($_GET["course"]) ?>"
                <? endif ?>
                />
            </div>
        </div>
        <div class="form-group form-group-lg">
            <label for="date" class="col-sm-2 col-sm-offset-2 control-label">Registration date</label>
            <div class="col-sm-4">
                <input type="date" id="date" class="form-control" name="date" placeholder="yyyy-mm-dd" min="2015-04-06"
                <? if (isset($_GET["date"])): ?>
                    value="<?= htmlspecialchars($_GET["date"]) ?>"
                <? endif ?>
                />
            </div>
        </div>
        <div class="form-group form-group-lg">
            <div class="col-sm-offset-4 col-sm-4">
                <button type="submit" id="search-button" class="btn btn-primary btn-lg">Will I Get In?</button>
            </div>
        </div>
    </form>
</div>

<?php include "../templates/footer.php" ?>