<?php

require_once "Section.php";

/**
* Represents a course and its historical availability data.
*/
class Course {

    private $dbh;
    private $subject_code;
    private $course_num;

    /**
     * Constructs a course.
     * 
     * @param PDO $dbh The database handle
     * @param string $subject_code The subject code of the class
     * @param int $course_num The course number of the class
     */
    public function __construct($dbh, $subject_code, $course_num) {

        $this->dbh = $dbh;

        if (preg_match("/^[a-z]{2,4}$/i", $subject_code)) {
            $this->subject_code = strtoupper($subject_code);
        } else {
            throw new UnexpectedValueException("Invalid subject code: $subject_code");
        }
        
        if (is_int($course_num) && $course_num > 0 && $course_num <= 999) {
            $this->course_num = $course_num;
        } else {
            throw new UnexpectedValueException("Invalid course number: $course_num");
        }
    }

    /**
     * Returns a string representation of the course.
     */
    public function __toString() {
        return $this->subject_code." ".$this->course_num;
    }

    /**
     * Returns the subject code of the course.
     */
    public function getSubjectCode() {
        return $this->subject_code;
    }

    /**
     * Returns the course number of the course.
     */
    public function getCourseNumber() {
        return $this->course_num;
    }

    /**
     * Returns an array of the course's sections for the given semester.
     * 
     * @param Semester $sem The semester to check
     */
    public function getSections($semester) {

        $sql = <<<SQL
            SELECT crn
            FROM sections
            WHERE subjectcode=:subject_code
                AND coursenumber=:course_num
                AND semester=:sem
SQL;
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(":subject_code", $this->subject_code);
        $stmt->bindValue(":course_num", $this->course_num);
        $stmt->bindParam(":sem", $semester->getCode());

        $stmt->execute();

        $sections = [];
        foreach ($stmt as $row) {
            $sections[] = new Section($this->dbh, $row["crn"], $semester);
        }

        return $sections;
    }

    /**
     * Returns an array of semesters that the class was offered.
     */
    public function getSemestersOffered() {

        $sql = <<<SQL
            SELECT DISTINCT semester
            FROM sections
            WHERE subjectcode=:subject_code
                AND coursenumber=:course_num
SQL;
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(":subject_code", $this->subject_code);
        $stmt->bindValue(":course_num", $this->course_num);

        $stmt->execute();

        $semesters = [];
        foreach ($stmt as $row) {
            $semesters[] = new Semester($this->dbh, $row["semester"]);
        }

        return $semesters;
    }

    /**
     * Returns the number of the course's sections with each availability status
     * on the given date.
     * 
     * @param DateTime $date The date to check
     */
    public function getAvailabilityOnDate($date) {

        $sql = <<<SQL
            SELECT semester,
                sectiontype,
                enrollmentstatus,
                COUNT(enrollmentstatus) AS count
            FROM sections INNER JOIN availability
                USING(crn, semester)
            WHERE subjectcode=:subject_code
                AND coursenumber=:course_num
                AND DATE(timestamp)=:date
            GROUP BY semester,
                sectiontype,
                enrollmentstatus
SQL;
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(":subject_code", $this->subject_code);
        $stmt->bindValue(":course_num", $this->course_num);
        $stmt->bindParam(":date", $date->format("Y-m-d"));

        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Returns the weekly availability of the course for all semesters.
     */
    public function getAllWeeklyAvailability() {

        $sql = <<<SQL
            SELECT semester,
                FLOOR(DATEDIFF(DATE(timestamp), registrationdate)/7) AS week,
                sectiontype,
                COUNT(enrollmentstatus) AS count
            FROM sections INNER JOIN availability using(crn, semester)
                INNER JOIN semesters using(semester)
            WHERE subjectcode=:subject_code
                AND coursenumber=:course_num
                AND DATE(timestamp)>=registrationdate
                AND enrollmentstatus>0
            GROUP BY semester, week, sectiontype
            ORDER BY registrationdate desc, week, sectiontype
SQL;
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(":subject_code", $this->subject_code);
        $stmt->bindValue(":course_num", $this->course_num);

        $stmt->execute();

        $result = [];
        foreach ($stmt as $row) {
            $sem = $row["semester"];
            $type = $row["sectiontype"];
            $week = $row["week"];
            $result[$sem][$type][$week] = intval($row["count"]);
        }

        foreach ($result as $sem => $sections) {

            $num_weeks = (new Semester($this->dbh, $sem))->getNumWeeks();
            foreach ($sections as $type => $section) {

                // Remove the last week of data because it might be incomplete
                unset($result[$sem][$type][$num_weeks]);

                // Fill in any missing values with 0
                for ($i = 0; $i < $num_weeks; $i++) {
                    if (!array_key_exists($i, $section)) {
                        $result[$sem][$type][$i] = 0;
                    }
                }

                ksort($result[$sem][$type]);
            }
        }

        return $result;
    }
}

?>