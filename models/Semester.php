<?php

/**
* Represents a semester.
*/
class Semester {

    private $dbh;
    private $code;

    /**
     * Constructs a semester.
     * 
     * @param PDO $dbh The database handle
     * @param string $code The semester's 4-character code (e.g. fa15)
     */
    public function __construct($dbh, $code) {

        $this->dbh = $dbh;

        if (preg_match("/^(fa|sp)[0-9]{2}$/i", $code)) {
            $this->code = strtolower($code);
        } else {
            throw new UnexpectedValueException("Invalid semester code: $code");
        }
    }

    /**
     * Returns the semester's term.
     */
    public function getTerm() {

        if (substr($this->code, 0, 2) == "fa") {
            return "fall";
        } else {
            return "spring";
        }
    }

    /**
     * Returns the semester's year.
     */
    public function getYear() {
        return intval("20".substr($this->code, 2, 2));
    }

    /**
     * Returns the semester's 4-character code.
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * Returns a string representation of the course.
     */
    public function __toString() {
        return getCode();
    }

    /**
     * Returns the date that registration begins.
     */
    public function getRegistrationDate() {

        $sql = "SELECT registrationdate FROM semesters WHERE semester=:code";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(":code", $this->code);
        
        $stmt->execute();

        return new DateTime($stmt->fetch()["registrationdate"]);
    }

    /**
     * Returns the date that instruction begins.
     */
    public function getInstructionDate() {

        $sql = "SELECT instructiondate FROM semesters WHERE semester=:code";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(":code", $this->code);
        
        $stmt->execute();

        return new DateTime($stmt->fetch()["instructiondate"]);
    }

    /**
     * Returns the number of weeks of data in the semester.
     */
    public function getNumWeeks() {

        // Fetches a sample CRN to make the availability query much faster
        $sql = "SELECT FLOOR(DATEDIFF(MAX(timestamp), :date)/7) AS week ".
                    "FROM availability WHERE semester=:code AND ".
                    "crn=(SELECT crn FROM sections WHERE semester=:code LIMIT 1)";
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":code", $this->code);
        
        $date = getRegistrationDate()->format("Y-m-d");
        $stmt->execute();

        return intval($stmt->fetch()["week"]);
    }
}

?>