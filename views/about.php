<?php include "../templates/header.php"; ?>

<div class="container">
    <div class="page-header">
        <h1>About</h1>
    </div>
    <p>
        <b>Can I Take This Class</b> is a tool for students at the
        University of Illinois at Urbana-Champaign to predict
        their chances of getting into the classes they want.
        It uses historical course registration data to predict
        when classes will fill up.
    </p>

    <h2>For Developers</h2>
    <p>
        Want to use these predictions in your own project?
        Check out the <a href="/docs/index">API</a>!
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

    <h2>History</h2>
    <p>
        Can I Take This Class was developed by Alex Cordonnier
        as a continuation of ClassMaster, a CS 411 final project
        developed by Clarence Elliott, Gaurang Jain, Sean Mulroe,
        and Alex Cordonnier.
    </p>
</div>

<?php include "../templates/footer.php"; ?>