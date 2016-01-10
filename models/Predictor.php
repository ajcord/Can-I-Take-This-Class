<?php

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
        
        // Format of return value. Values will be replaced.
        $result = [
            "on_date" => [
                "percent" => 1,
                "error" => 0,
            ],
            "after_date" => [
                "percent" => 1,
                "error" => 0,
            ],
        ];

        // TODO: handle when course is not offered

        // Get stats on the date
        
        $sql = <<<SQL
            SELECT sectiontype,
                percent,
                n,
                SQRT(percent*(1-percent)/n) AS error
            FROM (
                SELECT sectiontype,
                    -- Get the percentage of results with enrollmentstatus > 0
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
        $stmt->bindValue(":date", $this->date->format("Y-m-d"));

        $stmt->execute();

        foreach ($stmt as $row) {
            
            $percent = floatval($row["percent"]);
            $error = floatval($row["error"]);

            if ($percent < $result["on_date"]["percent"]) {
                $result["on_date"]["percent"] = $percent;
                $result["on_date"]["error"] = $error;
            }
        }
        
        // Get stats for after the date
        
        $sql = <<<SQL
            SELECT sectiontype,
                percent,
                n,
                SQRT(percent*(1-percent)/n) AS error
            FROM (
                SELECT sectiontype,
                    -- Get the percentage of results with enrollmentstatus > 0
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
        $stmt->bindValue(":date", $this->date->format("Y-m-d"));

        $stmt->execute();

        foreach ($stmt as $row) {
            
            $percent = floatval($row["percent"]);
            $error = floatval($row["error"]);

            if ($percent < $result["after_date"]["percent"]) {
                $result["after_date"]["percent"] = $percent;
                $result["after_date"]["error"] = $error;
            }
        }

        return $result;
    }
}

?>