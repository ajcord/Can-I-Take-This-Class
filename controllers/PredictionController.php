<?php

/**
 * Controls statistical predictions of class availability.
 */
class PredictionController {
    
    private $course;
    private $registration_date;

    /**
     * Constructs a PredictionController.
     * 
     * @param Course $course The course to predict
     * @param DateTime $registration_date The user's registration date
     */
    public function __construct($course, $registration_date) {

        $this->course = $course;
        $this->registration_date = $registration_date;
    }

    /**
     * Calculates the overall likelihood of getting into the class.
     */
    public function getOverallLikelihood() {
        
        // TODO
        return [
            "on_date" => [
                "percent" => 1,
                "error" => 0,
            ],
            "after_date" => [
                "percent" => 1,
                "error" => 0,
            ],
        ];

        // foreach ($result as $sectiontype) {
            
        //     $overall["on_date"]["percent"] = min($sectiontype["on_date"]["percent"],
        //         $overall["on_date"]["percent"]);

        //     $overall["after_date"]["percent"] = min($sectiontype["after_date"]["percent"],
        //         $overall["after_date"]["percent"]);
        // }
    }
}

?>