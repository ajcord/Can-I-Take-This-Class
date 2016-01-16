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
        Find out whether you'll get into the classes you want at UIUC.
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
                <button type="submit" id="search-button" class="btn btn-info btn-lg">Will I Get In?</button>
            </div>
        </div>
    </form>
</div>

<div class="container">
    <h2>Overview</h2>
    <p>
        <b>Can I Take This Class</b> is a tool for students at the
        <a href="http://illinois.edu">University of Illinois at Urbana-Champaign</a>
        to predict their chances of getting into the classes they want.
        It uses historical course registration data to predict
        when classes will fill up and when they will open up again.
    </p>
    <p>
        It was developed by
        <a href="http://alexcordonnier.com">Alex Cordonnier</a>
        as the successor to ClassMaster, a CS 411 final project
        developed with Clarence Elliott, Gaurang Jain, and Sean Mulroe.
    </p>

    <h2>How it works</h2>
    <p>
        First, it calculates the percentage of sections that were open
        in previous semesters around the equivalent registration date.
        More recent semesters are weighted more heavily
        because classes and demand change over time.
    </p>
    <p>
        Next, it calculates the percentage of sections
        that historically open up later during registration
        or even after classes start.
    </p>
    <p>
        Finally, assuming you need to get into one of each type of section,
        your chances of getting into a class are as good as
        the lowest section type's chances.
    </p>
    <p>
        Want to know more?
        Check out the source code on
        <a href="https://github.com/ajcord/Can-I-Take-This-Class">GitHub</a>.
    </p>

    <h2>What it doesn't do</h2>
    <p>
        Can I Take This Class works well for most classes,
        but there are a few things it can't do:
    </p>
    <ul>
        <li>
            Predict classes that haven't been offered before
            or were last offered before Fall 2015
        </li>
        <li>
            Predict specific sections, like if you really want
            the 11 AM lecture and not the 8 AM one
        </li>
        <li>
            Figure out restrictions on a section or course.
            Restricted courses are treated as if they are open.
        </li>
        <li>
            Register for you or tell you when a class opens up
        </li>
    </ul>
    <p>
        It works best on classes where you need one of every type of section.
        For other classes, you can use the table provided
        under the prediction to see what your chances would really be.
    </p>

    <h2>For developers</h2>
    <p>
        Want to use these predictions in your own project?
        Check out the
        <a href="https://github.com/ajcord/Can-I-Take-This-Class/wiki/API-Docs">API</a>!
    </p>
</div>

<?php include "../templates/footer.php" ?>