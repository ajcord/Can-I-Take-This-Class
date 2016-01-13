<?php include "../../templates/header.php"; ?>

<div class="container">
    <div class="page-header">
        <h1>API Docs</h1>
    </div>
    <p>
        Can I Take This Class offers a prediction API to enable other
        applications to build on this powerful data.
        The format of the API is outlined below.
    </p>

    <h2>Reference</h2>

        <h3>URL</h3>
        <p>
            <a href="http://canitakethisclass.com/api">http://canitakethisclass.com/api</a>
        </p>

        <h3>Format</h3>
        <p>
            JSON
        </p>

        <h3>Types</h3>
        <ul>
            <li>
                <code>Course</code>:
                    A string containing a 2-4 character subject code
                    (case insensitive) followed by a 3-digit course number.
            </li>
            <li>
                <code>Date</code>:
                    A string in the format "YYYY-MM-DD".
            </li>
            <li>
                <code>Chance</code>:
                    An object containing two floats in the range [0, 1]:
                    <code>percent</code>, the likelihood of an event; and
                    <code>error</code>, the standard error of the likelihood.
            </li>
            <li>
                <code>Prediction</code>:
                    An object containing two Chances:
                    <code>on_date</code>, the likelihood of being able
                    to register on the given date; and
                    <code>after_date</code>, the likelihood of being able
                    to register after the given date.
            </li>
        </ul>

        <h3>Parameters</h3>
        <ul>
            <li>
                <code>String courses</code>:
                    A comma-separated list of Courses.
            </li>
            <li>
                <code>Date date</code>:
                    The registration date.
            </li>
        </ul>

        <h3>Result</h3>
        <ul>
            <li>
                <code>String error</code>:
                    If an error occurred, contains a description
                    of the error. Will not exist if an error did not occur.
            </li>
            <li>
                <code>Prediction overall</code>:
                    The likelihood of getting into all of the requested courses.
            </li>
            <li>
                <code>Object courses</code>:
                    An object containing, for each requested course:

                    <ul>
                        <li>
                            <code>Prediction overall</code>:
                                The likelihood of getting into the course.
                        </li>
                        <li>
                            <code>Object sections</code>:
                                An object containing the Prediction
                                for each section in the course.
                        </li>
                    </ul>
            </li>
        </ul>

        <h3>Example</h3>
            <h4>Request URL</h4>
            <pre>http://canitakethisclass.com/api?courses=cs225,cs233&amp;date=2016-04-11</pre>

            <h4>Response</h4>
            <pre>
{
  "overall": {
    "on_date": {
      "percent": 0.9643,
      "error": 0.034408792480992
    },
    "after_date": {
      "percent": 0.25875,
      "error": 0.02229142148461
    }
  },
  "courses": {
    "cs225": {
      "overall": {
        "on_date": {
          "percent": 0.9643,
          "error": 0.034408792480992
        },
        "after_date": {
          "percent": 0.25875,
          "error": 0.02229142148461
        }
      },
      "sections": {
        "Laboratory-Discussion": {
          "on_date": {
            "percent": 0.9647,
            "error": 0.011427165922999
          },
          "after_date": {
            "percent": 0.5779,
            "error": 0.0088835322571266
          }
        },
        "Lecture": {
          "on_date": {
            "percent": 0.9643,
            "error": 0.034408792480992
          },
          "after_date": {
            "percent": 0.25875,
            "error": 0.02229142148461
          }
        }
      }
    },
    "cs233": {
      "overall": {
        "on_date": {
          "percent": 1,
          "error": 0
        },
        "after_date": {
          "percent": 0.58585,
          "error": 0.028675014339463
        }
      },
      "sections": {
        "Discussion\/Recitation": {
          "on_date": {
            "percent": 1,
            "error": 0
          },
          "after_date": {
            "percent": 0.6792,
            "error": 0.010438972608586
          }
        },
        "Lecture": {
          "on_date": {
            "percent": 1,
            "error": 0
          },
          "after_date": {
            "percent": 0.58585,
            "error": 0.028675014339463
          }
        }
      }
    }
  }
}</pre>

</div>

<?php include "../../templates/footer.php"; ?>