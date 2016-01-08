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

        if (preg_match("/^[0-9]{5}$/", $crn)) {
            $this->crn = intval($crn);
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
        return getColumn("subjectcode");
    }

    /**
     * Returns the course number of the section.
     */
    public function getCourseNumber() {
        return intval(getColumn("coursenumber"));
    }

    /**
     * Returns the type of the section.
     */
    public function getSectionType() {
        return getColumn("sectiontype");
    }

    /**
     * Returns the name of the section.
     */
    public function getName() {
        return getColumn("name");
    }

    /**
     * Gets the specified column for the section.
     */
    private function getColumn($col) {

        $sql = "SELECT :col FROM sections WHERE crn=:crn AND semester=:sem";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(":col", $col);
        $stmt->bindValue(":crn", $this->crn);
        $stmt->bindValue(":sem", $this->semester->getCode());

        $stmt->execute();

        $row = $stmt->fetch();
        if ($row == false) {
            throw new Exception("CRN $crn does not exist in semester $sem");
        }

        return $row[$col];
    }

    /**
     * Returns the availability of the section on the given date.
     * 
     * @param DateTime $date The date the check
     */
    public function getAvailabilityOnDate($date) {

        $sql = "SELECT enrollmentstatus FROM availability WHERE crn=:crn AND semester=:sem AND DATE(timestamp)=:date";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindValue(":crn", $this->crn);
        $stmt->bindValue(":sem", $this->semester->getCode());
        $stmt->bindParam(":date", $date);

        $date = $date->format("Y-m-d");
        $stmt->execute();

        return $stmt->fetch()["enrollmentstatus"];
    }

    /**
     * Returns the availability of the section on all dates.
     */
    public function getAllAvailability() {

        $sql = "SELECT DATE(timestamp) AS date, enrollmentstatus FROM availability WHERE crn=:crn AND semester=:sem";
        $stmt = $dbh->prepare($sql);
        $stmt->bindValue(":crn", $crn);
        $stmt->bindValue(":sem", $this->semester->getCode());

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}

?>