<?php

/**
* Represents a section and its historical availability data.
*/
class Section {

    private $dbh;
    private $crn;
    private $semester;

    /**
     * Constructs a section.
     * 
     * @param PDO $dbh The database handle
     * @param int $crn The CRN of the section
     * @param Semester $semester The semester the section is offered
     */
    public function __construct($dbh, $crn, $semester) {

        $this->dbh = $dbh;
        
        if (is_int($crn) && $crn >= 10000 && $crn <= 99999) {
            $this->crn = $crn;
        } else {
            throw new UnexpectedValueException("Invalid CRN: $crn");
        }

        $this->semester = $semester;
    }

    /**
     * Returns a string representation of the section.
     */
    public function __toString() {
        return $this->crn." ".$this->semester->getCode();
    }

    /**
     * Returns the CRN of the section.
     */
    public function getCRN() {
        return $this->crn;
    }

    /**
     * Returns the semester of the section.
     */
    public function getSemester() {
        return $this->semester;
    }

    /**
     * Returns the subject code of the section.
     */
    public function getSubjectCode() {
        return $this->getColumn("subjectcode");
    }

    /**
     * Returns the course number of the section.
     */
    public function getCourseNumber() {
        return intval($this->getColumn("coursenumber"));
    }

    /**
     * Returns the type of the section.
     */
    public function getSectionType() {
        return $this->getColumn("sectiontype");
    }

    /**
     * Returns the name of the section.
     */
    public function getName() {
        return $this->getColumn("name");
    }

    /**
     * Gets the specified column for the section.
     */
    private function getColumn($col) {

        $sql = <<<SQL
            SELECT $col
            FROM sections
            WHERE crn=:crn
                AND semester=:sem
SQL;
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(":crn", $this->crn);
        $stmt->bindValue(":sem", $this->semester->getCode());

        $stmt->execute();

        $value = $stmt->fetchColumn();
        if ($value === false) {
            throw new Exception("CRN $this->crn does not exist in semester $sem");
        }

        return $value;
    }

    /**
     * Returns the availability of the section on the given date.
     * 
     * @param DateTime $date The date to check
     */
    public function getAvailabilityOnDate($date) {

        $sql = <<<SQL
            SELECT enrollmentstatus
            FROM availability
            WHERE crn=:crn
                AND semester=:sem
                AND DATE(timestamp)=:date
SQL;
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(":crn", $this->crn);
        $stmt->bindValue(":sem", $this->semester->getCode());
        $stmt->bindParam(":date", $date->format("Y-m-d"));

        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * Returns the availability of the section on all dates.
     */
    public function getAllAvailability() {

        $sql = <<<SQL
            SELECT DATE(timestamp) AS date,
                enrollmentstatus
            FROM availability
            WHERE crn=:crn
                AND semester=:sem
SQL;
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(":crn", $this->crn);
        $stmt->bindValue(":sem", $this->semester->getCode());

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}

?>