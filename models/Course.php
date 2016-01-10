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
     * Returns the availability of the section on all dates.
     * 
     * @param array(Semester) $semesters An array of the semesters to fetch
     */
    public function getAllAvailabilityForSemesters($semesters) {

        $sql = <<<SQL
            SELECT DATE(timestamp) AS date,
                sectiontype,
                enrollmentstatus,
                COUNT(enrollmentstatus) AS count
            FROM sections INNER JOIN availability
                USING(crn, semester)
            WHERE subjectcode=:subject_code
                AND coursenumber=:course_num
                AND semester=:sem
            GROUP BY date,
                sectiontype,
                enrollmentstatus
SQL;
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(":crn", $this->crn);
        $stmt->bindValue(":sem", $sem);

        $result = [];
        foreach ($semesters as $semester) {
            $sem = $semester->getCode();
            $stmt->execute();
            $result[$sem] = $stmt->fetchAll();
        }

        return $result;
    }
}

?>