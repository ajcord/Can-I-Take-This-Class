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
        // TODO: fix Y2.1K bug by year 2099
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
        return $this->getCode();
    }

    /**
     * Returns the date that registration begins.
     */
    public function getRegistrationDate() {
        return new DateTime($this->getColumn("registrationdate"));
    }

    /**
     * Returns the date that instruction begins.
     */
    public function getInstructionDate() {
        return new DateTime($this->getColumn("instructiondate"));
    }

    /**
     * Gets the specified column for the semester.
     */
    private function getColumn($col) {

        // PDO can't bind column or table names, so it has to be interpolated
        $sql =  <<<SQL
            SELECT $col
            FROM semesters
            WHERE semester=:code
SQL;
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(":code", $this->code);
        
        $stmt->execute();

        return new $stmt->fetchColumn();
    }

    /**
     * Returns the number of weeks of data in the semester.
     */
    public function getNumWeeks() {

        // Fetches a sample CRN to make the availability query much faster
        $sql = <<<SQL
            SELECT FLOOR(DATEDIFF(MAX(timestamp), :date)/7) AS week
            FROM availability
            WHERE semester=:code
                AND crn=(
                    SELECT crn
                    FROM sections
                    WHERE semester=:code
                    LIMIT 1
                )
SQL;
        $stmt = $this->dbh->prepare($sql);
        $stmt->bindParam(":date", $date);
        $stmt->bindParam(":code", $this->code);
        
        $date = $this->getRegistrationDate()->format("Y-m-d");
        $stmt->execute();

        return intval($stmt->fetchColumn());
    }

    /**
     * Given a registration date for an arbitrary semester,
     * returns an array of corresponding registration date for prior semesters.
     */
    public static function adjustDate($dbh, $date) {

        $sql = <<<SQL
            SELECT date_add(registrationdate,
                interval datediff(:date,
                    (
                        SELECT registrationdate
                        FROM semesters
                        WHERE registrationdate<=:date
                        ORDER BY registrationdate DESC
                        LIMIT 1)
                    )
                day) AS date
            FROM semesters
            WHERE registrationdate<=:date
            ORDER BY registrationdate
SQL;
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(":date", $date->format("Y-m-d"));

        $stmt->execute();

        $dates = [];
        foreach ($stmt as $row) {
            $dates[] = new DateTime($row["date"]);
        }

        return $dates;
    }
}

?>