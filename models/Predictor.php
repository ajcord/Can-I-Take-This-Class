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
     * Calculates the likelihood of getting into the course.
     */
    public function getOverallLikelihood() {
        return $this->getLikelihood(false);
    }

    /**
     * Calculates the likelihood of getting into each section type.
     */
    public function getItemizedLikelihood() {
        return $this->getLikelihood(true);
    }

    /**
     * Calculates the likelihood of getting into the course
     * or each section type.
     */
    private function getLikelihood($itemized) {
        
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
                    INNER JOIN sections USING (crn, semester)
                    INNER JOIN semesters USING (semester)
                WHERE subjectcode=:subject_code
                    AND coursenumber=:course_num
                    AND DATE(timestamp)>=registrationdate
                    AND DATE(timestamp)>=DATE_SUB(:date, INTERVAL 3 DAY)
                    AND DATE(timestamp)<DATE_ADD(:date, INTERVAL 4 DAY)
                GROUP BY sectiontype
            ) AS t
SQL;

        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(":subject_code", $this->course->getSubjectCode());
        $stmt->bindValue(":course_num", $this->course->getCourseNumber());

        $itemized_likelihood = $this->calculateItemizedLikelihood($stmt, $adjusted_dates);
        if ($itemized) {
            $result["on_date"] = $itemized_likelihood;
        } else {
            $result["on_date"] = $this->calculateOverallLikelihood($itemized_likelihood);
        }
        
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
                    INNER JOIN sections USING (crn, semester)
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

        $itemized_likelihood = $this->calculateItemizedLikelihood($stmt, $adjusted_dates);
        if ($itemized) {
            $result["after_date"] = $itemized_likelihood;
        } else {
            $result["after_date"] = $this->calculateOverallLikelihood($itemized_likelihood);
        }

        return $result;
    }

    /**
     * Given a SQL statemtent handle to get a single semester's percentage
     * for each section type, calculates the weighted average of
     * several semester's percentages along with the error.
     */
    private function calculateItemizedLikelihood($stmt, $adjusted_dates) {

        $result = [];

        // Must be bound here because of scope
        $stmt->bindParam(":date", $dateFormatted);

        $sem_count = 0;

        foreach ($adjusted_dates as $date) {

            $dateFormatted = $date->format("Y-m-d");
            $stmt->execute();

            // Pick the smallest percentage and its corresponding error
            $min_percent = [];
            $min_percent_error = [];
            $offered = false;

            foreach ($stmt as $row) {
                
                $type = $row["sectiontype"];
                $percent = floatval($row["percent"]);
                $error = floatval($row["error"]);

                if (!isset($min_percent[$type])) {
                    $min_percent[$type] = 1;
                    $min_percent_error[$type] = 0;
                }

                if ($percent < $min_percent[$type]) {
                    $min_percent[$type] = $percent;
                    $min_percent_error[$type] = $error;
                }

                $offered = true;
            }

            if (!$offered) {
                // Don't calculate for semesters it wasn't offered
                continue;
            }

            foreach (array_keys($min_percent) as $type) {
                
                // Remember:
                // E(aX + bY) = a * E(X) + b * E(Y)
                // Var(aX + bY) = a^2 * Var(X) + b^2 * Var(Y)
                // SD(aX + bY) = sqrt(a^2 * SD(X)^2 + b^2 * SD(Y)^2)
                $result[$type]["percent"] += $min_percent[$type];
                $variance = $result[$type]["error"]**2 +
                    $min_percent_error[$type]**2;

                if ($sem_count > 0) {
                    $result[$type]["percent"] *= 0.5;
                    $variance *= 0.25;
                }

                $result[$type]["error"] = sqrt($variance);
            }

            $sem_count++;
        }

        return $result;
    }

    /**
     * Given the likelihood of each section over all semesters,
     * calculates the likelihood of getting into the course as a whole.
     */
    private function calculateOverallLikelihood($itemized_likelihood) {

        $result = [
            "percent" => 1,
            "error" => 0,
        ];

        foreach ($itemized_likelihood as $type) {

            $percent = floatval($type["percent"]);
            $error = floatval($type["error"]);

            if ($percent < $result["percent"]) {
                $result["percent"] = $percent;
                $result["error"] = $error;
            }
        }

        return $result;
    }
}

?>