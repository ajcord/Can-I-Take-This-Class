<?php include "../templates/header.php" ?>

<div class="jumbotron text-center" id="main-jumbotron">

    <? if (isset($_GET["status"])):
        $status = $_GET["status"] ?>

        <? if ($status == "invalid_course"): ?>
            <div class="container">
                <div class="alert alert-danger">
                    Invalid course. Enter a course in one of the following formats:
                    SUBJ 123, SUBJ123, subj 123, subj123.
                </div>
            </div>
        <? elseif ($status == "invalid_date"): ?>
            <div class="container">
                <div class="alert alert-danger">
                    Invalid date. Enter a date that occurs on or after
                    April 6, 2015.
                </div>
            </div>
        <? elseif ($status == "course_does_not_exist"): ?>
            <div class="container">
                <div class="alert alert-danger">
                    The selected course does not exist in any semester on record.
                </div>
            </div>
        <? endif ?>

    <? endif ?>

    <h1>Can I Take This Class?</h1>

    <p>
        Find out whether you'll get into the classes you want at UIUC.
    </p>
    
    <form class="form-horizontal" action="/prediction" method="GET">
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
                <input type="date" id="date" class="form-control" name="date" placeholder="Enter your registration date" min="2015-04-06"
                <? if (isset($_GET["date"])): ?>
                    value="<?= htmlspecialchars($_GET["date"]) ?>"
                <? endif ?>
                />
            </div>
        </div>
        <div class="form-group form-group-lg">
            <div class="col-sm-offset-4 col-sm-4">
                <button type="submit" id="search-button" class="btn btn-warning btn-lg">Will I Get In?</button>
            </div>
        </div>
    </form>
</div>

<div class="container">
    <h2>About</h2>
    <p>
        <b>Can I Take This Class</b> is a tool for students at the
        University of Illinois at Urbana-Champaign to predict
        their chances of getting into the classes they want.
        It uses historical course registration data to predict
        when classes will fill up.
    </p>

    <h2>How It Works</h2>
    <p>
        First, it calculates the percentage of open sections
        on the days surrounding your corresponding registration date
        in previous semesters.
        More recent semesters are weighted more heavily
        because classes and demand change over time.
    </p>
    <p>
        Next, it calculates the percentage of sections that open up
        later during registration or after classes start.
        Even if a class is full on the day you register,
        it might eventually become available due to
        students dropping and sections being added.
    </p>
    <p>
        Finally, assuming you need to get into one of each type of section
        (e.g. Lecture, Discussion, etc.),
        your chances of getting into a class are only as good as
        the lowest section's chances.
    </p>
    <p>
        There are some things that it can't do.
        You can't select a specific section you want (e.g. Tuesday 3pm)
        because days and times vary by semester.
        All sections of a given type are treated equally, without regard
        to restrictions.
        Classes where not every section type is required,
        such as Special Topics classes, aren't predicted accurately.
        New classes are impossible to predict because there is no
        historical data to use.
        However, most classes should provide a good estimate of your
        chances.
    </p>

    <h2>For Developers</h2>
    <p>
        Want to use these predictions in your own project?
        Check out the <a href="/docs/index">API</a>!
        The project's source code is available on
        <a href="https://github.com/ajcord/CS411-Project">GitHub</a>.
    </p>

    <h2>History</h2>
    <p>
        Can I Take This Class was developed by Alex Cordonnier
        as a continuation of ClassMaster, a CS 411 final project
        developed by Clarence Elliott, Gaurang Jain, Sean Mulroe,
        and Alex Cordonnier.
    </p>
</div>

<?php include "../templates/footer.php" ?>