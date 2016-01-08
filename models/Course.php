<?php

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
        
        if (preg_match("/^[0-9]{3}$/")) {
            $this->course_num = intval($course_num);
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
     * Returns an array of the course's sections for the given semester.
     * 
     * @param string $sem The semester to check
     */
    public function getSections($sem) {

        $sql = "SELECT crn FROM sections WHERE subjectcode=:subject_code AND coursenumber=:course_num AND semester=:sem";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(":subject_code", $this->subject_code);
        $stmt->bindValue(":course_num", $this->course_num);
        $stmt->bindParam(":sem", $sem->getCode());

        $stmt->execute();

        $sections = [];
        foreach ($stmt as $row) {
            $sections[] = new Section($this->dbh, $row["crn"], $sem);
        }

        return $sections;
    }

    // /**
    //  * Returns an array of codes of all semesters that the class was offered.
    //  */
    // public function getSemestersOffered() {

    //     $sql = "SELECT DISTINCT semester FROM sections WHERE subjectcode=:subject_code AND coursenumber=:course_num";
    //     $stmt = $this->dbh->prepare($sql);
    //     $stmt->bindValue(":subject_code", $this->subject_code);
    //     $stmt->bindValue(":course_num", $this->course_num);

    //     $stmt->execute();

    //     return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    // }
}

?>