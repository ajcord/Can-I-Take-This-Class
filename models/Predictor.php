<?php

require_once "Semester.php";

/**
 * Makes statistical predictions of class availability.
 */
class Predictor {
    
    private $dbh;
    private $course;
    private $date;

    /**
     * Constructs a Predictor.
     * 
     * @param Course $course The course to predict
     * @param DateTime $date The date to use in calculations
     */
    public function __construct($dbh, $course, $date) {

        $this->dbh = $dbh;
        $this->course = $course;
        $this->date = $date;
    }

    /**
     * Calculates the overall likelihood of getting into the class.
     */
    public function getOverallLikelihood() {
        
        $result = [];

        // Get a list of equivalent registration dates
        $adjusted_dates = Semester::adjustDate($this->dbh, $this->date);

        // Get stats on the given date
        $sql = <<<SQL
            SELECT sectiontype,
                percent,
                SQRT(percent*(1-percent)/n) AS error
            FROM (
                SELECT sectiontype,
                    -- Hack to get the percentage of non-closed sections
                    AVG(IF(enrollmentstatus>0,1,0)) AS percent,
                    COUNT(*) AS n
                FROM availability
                    INNER JOIN sections
                    USING(crn, semester)
                WHERE subjectcode=:subject_code
                    AND coursenumber=:course_num
                    AND DATE(timestamp)>=DATE_SUB(:date, INTERVAL 3 DAY)
                    AND DATE(timestamp)<DATE_ADD(:date, INTERVAL 4 DAY)
                GROUP BY sectiontype
            ) AS t
SQL;

        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(":subject_code", $this->course->getSubjectCode());
        $stmt->bindValue(":course_num", $this->course->getCourseNumber());

        $result["on_date"] = $this->calculateLikelihood($stmt, $adjusted_dates);
        
        // Get stats after the given date
        $sql = <<<SQL
            SELECT sectiontype,
                percent,
                SQRT(percent*(1-percent)/n) AS error
            FROM (
                SELECT sectiontype,
                    -- Hack to get the percentage of non-closed sections
                    AVG(IF(enrollmentstatus>0,1,0)) AS percent,
                    COUNT(*) AS n
                FROM availability
                    INNER JOIN sections
                    USING(crn, semester)
                WHERE subjectcode=:subject_code
                    AND coursenumber=:course_num
                    AND DATE(timestamp)>=:date
                    AND DATE(timestamp)<(
                        SELECT instructiondate
                        FROM semesters
                        WHERE registrationdate<=:date
                        ORDER BY registrationdate DESC
                        LIMIT 1
                    )
                GROUP BY sectiontype
            ) AS t
SQL;
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(":subject_code", $this->course->getSubjectCode());
        $stmt->bindValue(":course_num", $this->course->getCourseNumber());

        $result["after_date"] = $this->calculateLikelihood($stmt, $adjusted_dates);

        return $result;
    }

    /**
     * Given a SQL statemtent handle to get a single semester's percentage
     * for each section type, calculates the weighted average of
     * several semester's percentages along with the error.
     */
    private function calculateLikelihood($stmt, $adjusted_dates) {

        $result = [
            "percent" => 0,
            "error" => 0,
        ];

        // Must be bound here because of scope
        $stmt->bindParam(":date", $dateFormatted);

        $sem_count = 0;

        foreach ($adjusted_dates as $date) {

            $dateFormatted = $date->format("Y-m-d");
            $stmt->execute();

            // Pick the smallest percentage and its corresponding error
            $min_percent = 1;
            $min_percent_error = 0;
            $offered = false;

            foreach ($stmt as $row) {
                
                $percent = floatval($row["percent"]);
                $error = floatval($row["error"]);

                if ($percent < $min_percent) {
                    $min_percent = $percent;
                    $min_percent_error = $error;
                }

                $offered = true;
            }

            if (!$offered) {
                // Don't calculate for semesters it wasn't offered
                continue;
            }

            // Remember:
            // E(aX + bY) = a * E(X) + b * E(Y)
            // Var(aX + bY) = a^2 * Var(X) + b^2 * Var(Y)
            // SD(aX + bY) = sqrt(a^2 * SD(X)^2 + b^2 * SD(Y)^2)
            $result["percent"] += $min_percent;
            $result["error"] = $result["error"]**2 + $min_percent_error**2;

            if ($sem_count > 0) {
                $result["percent"] *= 0.5;
                $result["error"] *= 0.25;
            }

            $result["error"] = sqrt($result["error"]);

            $sem_count++;
        }

        return $result;
    }
}

?>