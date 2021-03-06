<?php

    function _sortVitalsArray($a, $b) {
        // error_log("Sort - A = " . json_encode( $a));
        // error_log("Sort - B = " . json_encode( $b));
        /// return (strtotime($a["DateTaken"]) <= strtotime($b["DateTaken"]));
        return true;
    }

class Patient extends Model
{

    /* Retrieve all patient information for all patients from the "Patient" table */
    /* $this->table = "Patient" */
    function selectAll ($patientId = NULL) {
        if (DB_TYPE == 'sqlsrv' || DB_TYPE == 'mssql') {
            $query = "SELECT ID = Patient_ID, Name = (First_Name + ' ' + ISNULL(Middle_Name,'') + ' ' + Last_Name), " .
                     "Age = DATEDIFF(YY, DOB, GETDATE()) - CASE WHEN( (MONTH(DOB)*100 + DAY(DOB)) > (MONTH(GETDATE())*100 + DAY(GETDATE())) ) THEN 1 ELSE 0 END, " .
                     "DOB = CONVERT(VARCHAR(10), DOB, 101), Gender, Last_Name as lname, First_Name as fname, p.DFN as dfn " .
                     "FROM " . $this->_table . " p";


            $query = "SELECT ID = Patient_ID, Name = '', Age = '', DOB = '', Gender = '', lname = '', fname = '', p.DFN as dfn FROM " . $this->_table . " p";

            if ($patientId != NULL) {
                $query .= " where p.Patient_ID = '$patientId'";
            }
        }
        // error_log("Patient Model - selectAll - $query");
        return $this->query($query);
    }

    function selectByPatientId ($patientId)
    {
        if (DB_TYPE == 'sqlsrv' || DB_TYPE == 'mssql') {
            
            $query = "SELECT id = Patient_ID, name = (First_Name + ' ' + ISNULL(Middle_Name,'') + ' ' + Last_Name), " .
                     "Age = DATEDIFF(YY, DOB, GETDATE()) - CASE WHEN( (MONTH(DOB)*100 + DAY(DOB)) > (MONTH(GETDATE())*100 + DAY(GETDATE())) ) THEN 1 ELSE 0 END, " .
                     "DOB = CONVERT(VARCHAR(10), DOB, 101), Gender, Last_Name as lname, First_Name as fname, p.DFN as DFN " .
                     "FROM " . $this->_table . " p " . "WHERE p.Patient_ID = '" .
                     $patientId . "'";
            $query = "SELECT id = Patient_ID, name = '', Age = '', DOB = '', p.DFN as DFN FROM " . $this->_table . " p " . "WHERE p.Patient_ID = '$patientId'";
        }
// error_log("Patient Model - selectByPatientId - $query");
                $retVal = $this->query($query);
                $DFN = $retVal[0]["DFN"];
                $nodevista = new NodeVista();
                $PatientInfo = $nodevista->get("patient/$DFN");
                $pi = json_decode($PatientInfo);

                $dob = new DateTime($pi->{'dob'});
                $dobString = date_format($dob, 'd/m/Y');

                $retVal[0]["name"] = $pi->{'name'};
                $retVal[0]["Age"] = $pi->{'age'};
                $retVal[0]["DOB"] = $dobString;
                $retVal[0]["Gender"] = $pi->{'gender'};

// error_log("Patient Model - selectByPatientId - Results - " . json_encode($retVal[0]));
        return $retVal;
    }

    function selectHistory ($patiendId)
    {
        /*
         * if(DB_TYPE == 'sqlsrv' || DB_TYPE == 'mssql'){ $query = "select
         * l.Description as DiseaseType, l1.Name as DiseaseCat, Chemo_ID as
         * Chemo, Radiation_ID as Radiation, ". "PerfStat = l2.Description + '-'
         * + l2.Name, l3.Name as TreatIndic, l4.Name as Protocol ". "from
         * Patient_History ph, LookUp l, LookUp l1, LookUp l2, LookUp l3, LookUp
         * l4 ". "where ph.Patient_ID = '". $patiendId . "' and
         * ph.Disease_Type_ID = l.Lookup_ID and ph.Disease_Cat_ID = l1.Lookup_ID
         * " . "and ph.Performance_ID = l2.Lookup_ID and
         * ph.Treatment_Indicator_ID = l3.Lookup_ID and ph.Protocol_ID =
         * l4.Lookup_ID"; } else if(DB_TYPE == 'mysql'){ $query = "select
         * l.Description as DiseaseType, l1.`Name` as DiseaseCat, Chemo_ID as
         * Chemo, Radiation_ID as Radiation, ". "concat_ws('-',
         * l2.`Description`,l2.`Name`) as PerfStat, l3.`Name` as TreatIndic,
         * l4.`Name` as Protocol ". "from Patient_History ph, LookUp l, LookUp
         * l1, LookUp l2, LookUp l3, LookUp l4 ". "where ph.Patient_ID = '".
         * $patiendId . "' and ph.Disease_Type_ID = l.Lookup_ID and
         * ph.Disease_Cat_ID = l1.Lookup_ID " . "and ph.Performance_ID =
         * l2.Lookup_ID and ph.Treatment_Indicator_ID = l3.Lookup_ID and
         * ph.Protocol_ID = l4.Lookup_ID"; } return $this->query($query);
         */
    }

    /**
     *
     * @param stdClass $formData            
     * @return array
     */

    public function terminateOutstandingRegimens($patientId, $templateId) {
        $query = "
            SELECT PAT_ID AS id, Template_ID AS Template_ID 
            FROM Patient_Assigned_Templates 
            WHERE Date_Ended_Actual is null and Patient_ID = '$patientId'
        ";
    // error_log("terminateOutstandingRegimens $query");
        $results = $this->query($query);

        if ($results) {
            foreach ($results as $result) {
                $id = $result['id'];
                $dateEndedValue = 'CONVERT(VARCHAR,GETDATE(),121)';
                $query = "
                    UPDATE Patient_Assigned_Templates SET 
                        Date_Ended = $dateEndedValue, 
                        Date_Ended_Actual = $dateEndedValue
                    WHERE PAT_ID = '$id'
                ";
    // error_log("terminateOutstandingRegimens $query");
    /////////////$this->query($query);
        
        /**
         *
         * @todo This should be handled by the controller or some
         *       "Order" model
         */
                $query = "
                    UPDATE Order_Status SET 
                        Order_Status = 'Cancelled'
                    WHERE Patient_ID = '$patientId' 
                        AND Template_ID = '$templateId'
                ";
    // error_log("terminateOutstandingRegimens $query");
    /////////////$this->query($query);
            }
        }
    }

        public function overrideBSAFormula( $patientID, $usr) {
            $query   = "update Patient_BSA
            Set 
            Active = 0,
            Date_Changed = GETDATE(),
            UserName = '$usr'
            where Patient_ID = '$patientID' and Active = 1";
            $this->query($query);
        }

        public function addNewBSAFormula($patientID, $wt, $BSAFormula, $usr) {
            $query   = "INSERT INTO Patient_BSA
          (Patient_ID,WeightFormula,BSAFormula,Active,Date_Assigned,Date_Changed,UserName)
          VALUES ('$patientID', '$wt', '$BSAFormula', 1, GETDATE(), GETDATE(), '$usr')";
            $this->query($query);
        }

        public function _setBSA( $patientID, $wt, $BSAFormula, $usr) {
            // error_log("_setBSA - $patientID, $wt, $BSAFormula, $usr");
            $this->overrideBSAFormula($patientID, $usr);
            $this->addNewBSAFormula($patientID, $wt, $BSAFormula, $usr);
        }


    
    
    
    
    
    public function savePatientTemplate($formData) {
// error_log("savePatientTemplate Form - " . json_encode($formData));


        $patientId = $formData->PatientID;
        $templateId = $formData->TemplateID;
        $dateApplied = $formData->DateApplied;
        $dateStarted = $formData->DateStarted;
        $dateEnded = $formData->DateEnded;
        $goal = $formData->Goal;
        $ConcurRadTherapy = $formData->ConcurRadTherapy;
        $performanceStatus = $formData->PerformanceStatus;
        $weightFormula = $formData->WeightFormula;
        $bsaFormula = $formData->BSAFormula;
        $clinicalTrial = $formData->ClinicalTrial;
        $id = $formData->id;
        $isActive = 0;
        $ApprovedByUser = "";

        $AssignedByRoleID = $_SESSION["rid"];
        $AssignedByUser = $_SESSION["AccessCode"];
        if (!$_SESSION[ 'Preceptee' ]) {
            $ApprovedByUser = $_SESSION["AccessCode"];
            $isActive = 1;
        }

        if ( "POST" == $_SERVER[ 'REQUEST_METHOD' ] ) {
            if ($ApprovedByUser != "") {
                $this->terminateOutstandingRegimens($patientId, $templateId);
            }
            $query = "
                INSERT INTO Patient_Assigned_Templates (
                    Patient_ID,
                    Template_ID,
                    Date_Applied,
                    Date_Started,
                    Date_Ended,
                    Is_Active,
                    AssignedByRoleID,
                    AssignedByUser,
                    ApprovedByUser,
                    Goal,
                    ConcurRadTherapy,
                    Status,
                    Perf_Status_ID,
                    Weight_Formula,
                    BSA_Method,
                    Clinical_Trial
                ) values (
                    '$patientId',
                    '$templateId',
                    '$dateApplied',
                    '$dateStarted',
                    '$dateEnded',
                    $isActive,
                    '$AssignedByRoleID',
                    '$AssignedByUser',
                    '$ApprovedByUser',
                    '$goal',
                    '$ConcurRadTherapy',
                    'Ordered',
                    '$performanceStatus',
                    '$weightFormula',
                    '$bsaFormula',
                    '$clinicalTrial'
                )";
// error_log("Patient Model - savePatientTemplate() - POST process - $query");
            $retValue = $this->query($query);
// error_log("Patient Model - savePatientTemplate() - POST process - return from Query - \n\n" . json_encode($retValue));

            /**
             * OrdersNotify in app/workflow.php
             */
            if ($ApprovedByUser != "") {
                OrdersNotify($patientId, $templateId, $dateApplied, $dateStarted, $dateEnded, $goal, $clinicalTrial, $performanceStatus);
                $this->_setBSA( $patientId, $weightFormula, $bsaFormula, $ApprovedByUser);
            }
        }
        else if ( "PUT" == $_SERVER[ 'REQUEST_METHOD' ] ) {
            if ($ApprovedByUser != "") {
                $this->terminateOutstandingRegimens($patientId, $templateId);
                $query = "select AssignedByUser from Patient_Assigned_Templates where PAT_ID = '$id'";
                $retValue = $this->query($query);
                $abu = $retValue[0]['AssignedByUser'];
                if ($abu !== "") {
                    $AssignedByUser = $abu;
                }
            }

            $query = "
                UPDATE Patient_Assigned_Templates SET 
                    Patient_ID = '$patientId',
                    Template_ID = '$templateId',
                    Date_Applied = '$dateApplied',
                    Date_Started = '$dateStarted',
                    Date_Ended = '$dateEnded',
                    Is_Active = $isActive,
                    AssignedByRoleID = '$AssignedByRoleID',
                    AssignedByUser = '$AssignedByUser',
                    ApprovedByUser = '$ApprovedByUser',
                    Goal = '$goal',
                    ConcurRadTherapy = '$ConcurRadTherapy',
                    Status = 'Ordered',
                    Perf_Status_ID = '$performanceStatus',
                    Weight_Formula = '$weightFormula',
                    BSA_Method = '$bsaFormula',
                    Clinical_Trial = '$clinicalTrial'
                    where PAT_ID = '$id'";
            $retValue = $this->query($query);
            if ($ApprovedByUser != "") {
                OrdersNotify($patientId, $templateId, $dateApplied, $dateStarted, $dateEnded, $goal, $clinicalTrial, $performanceStatus);
            }
        }

        $lookup = new LookUp();
        foreach ($formData->Amputations as $amputation) {
            $lookup->save(30, $patientId, $amputation);
        }

        $query = "
            SELECT PAT_ID AS id 
            FROM Patient_Assigned_Templates 
            WHERE Patient_ID = '$patientId' 
                AND Template_ID ='$templateId' 
                AND Date_Ended_Actual is NULL
        ";
        $result = $this->query($query);
        return $result;
    }


function convertReason2ID($Reason) {
    if (0 === intval($Reason)) {
        $query = "select WorkFlowID as Reason from Workflows where WorkFlowName = '$Reason'";
    }
    return $this->query($query);
}


    function getCurrentAndHistoricalPatientTemplates( $patientID ) {
        $query = "
            SELECT 
            case when pat.PAT_ID is not null then pat.PAT_ID else '' end as id
            ,pat.Patient_ID as PatientID
            ,case when pat.Date_Applied is not null then CONVERT(VARCHAR(10), pat.Date_Applied, 101) else '' end as DateApplied
            ,case when pat.Date_Started is not null then CONVERT(VARCHAR(10), pat.Date_Started, 101) else '' end as DateStarted
            ,case when pat.Date_Ended is not null then CONVERT(VARCHAR(10), pat.Date_Ended, 101) else '' end as DateEnded
            ,case when pat.Date_Ended_Actual is not null then CONVERT(VARCHAR(10), pat.Date_Ended_Actual, 101) else '' end as DateEndedActual
            ,case when mt.Template_ID is not null then mt.Template_ID else '' end as TemplateID
            ,case when l1.Description is not null then l1.Description else '' end as TemplateName
            ,case when l2.Description is not null then l2.Description else '' end as TemplateDescription
            ,EoTS_ID as EotsID
            ,pat.AssignedByUser
            ,pat.ApprovedByUser
            FROM Patient_Assigned_Templates pat
            INNER JOIN Master_Template mt ON mt.Template_ID = pat.Template_ID
            INNER JOIN LookUp l1 ON l1.Lookup_ID = mt.Regimen_ID
            left outer JOIN LookUp l2 ON l2.Name = convert(nvarchar(max),mt.Regimen_ID)
            LEFT JOIN EoTS eots on EoTS.PAT_ID = pat.PAT_ID
            WHERE pat.Patient_ID = '$patientID'
            Order By DateEndedActual Desc, DateEnded Desc, DateStarted Desc
        ";

        return $this->query($query);
    }


    function getPriorPatientTemplates ($id)
    {
            $query = "SELECT mt.Template_ID as templateId, 
                pat.PAT_ID as ID, 
                case when l2.Name is not null then l2.Description else l1.Description end as templatename, 
                case when pat.Date_Ended_Actual is not null then 
                    CONVERT(datetime, pat.Date_Ended_Actual, 104) else 
                    CONVERT(datetime, pat.Date_Ended, 104) end as LastDate,
                CONVERT(VARCHAR(10), pat.Date_Started, 101) as started, 
                CONVERT(VARCHAR(10), pat.Date_Ended, 101) as ended, 
                CONVERT(VARCHAR(10), pat.Date_Applied, 101) as applied, 
                eots.EoTS_ID as EOTS_ID, 
                CONVERT(VARCHAR(10), pat.Date_Ended_Actual, 101) as ended_actual 
                FROM Patient_Assigned_Templates pat 
                INNER JOIN Master_Template mt ON mt.Template_ID = pat.Template_ID
                INNER JOIN LookUp l1 ON l1.Lookup_ID = mt.Regimen_ID
                LEFT OUTER JOIN LookUp l2 ON l2.Name = convert(nvarchar(max),mt.Regimen_ID)
                LEFT JOIN EoTS eots ON eots.PAT_ID = pat.PAT_ID
                WHERE pat.Patient_ID = '$id' 
                ORDER BY CONVERT(datetime, pat.Date_Started, 104) Desc, LastDate Desc;";

        return $this->query($query);
    }

    function getAdminDatesForTemplate ($templateId)
    {
        $query = "SELECT Admin_Date FROM Master_Template " .
                 "WHERE Regimen_ID = (SELECT Regimen_ID FROM Master_Template where Template_ID = '" .
                 $templateId . "') " . "AND Admin_Date IS NOT NULL";
        
        return $this->query($query);
    }

    function isAdminDate ($templateId, $currDate)
    {
        $query = "SELECT Admin_Date FROM Master_Template " .
                 "WHERE Regimen_ID = (SELECT Regimen_ID FROM Master_Template where Template_ID = '" .
                 $templateId . "') " . "AND Admin_Date ='" . $currDate . "'";
        
        return $this->query($query);
    }

    function getPatientDetailInfo ($id)
    {
        if (DB_TYPE == 'sqlsrv' || DB_TYPE == 'mssql') {
            $query = "SELECT case when mt.Template_ID is not null then mt.Template_ID else '' end as TemplateID, 
case when l2.Description is not null then l2.Description else '' end as TemplateDescription, 
case when l1.Description is not null then l1.Description else '' end as TemplateName, 
case when pat.Date_Started is not null then CONVERT(VARCHAR(10), pat.Date_Started, 101) else '' end as TreatmentStart, 
case when pat.Date_Ended is not null then CONVERT(VARCHAR(10), pat.Date_Ended, 101) else '' end as TreatmentEnd, 
case when pat.Date_Ended_Actual is not null then CONVERT(VARCHAR(10), pat.Date_Ended_Actual, 101) else '' end as TreatmentEndActual, 
case when pat.Goal is not null then pat.Goal else '' end as Goal, 
case when pat.Clinical_Trial is not null then pat.Clinical_Trial else '' end as ClinicalTrial, 
case when pat.Weight_Formula is not null then pat.Weight_Formula else '' end as WeightFormula, 
case when pat.BSA_Method is not null then pat.BSA_Method else '' end as BSAFormula, 
case when l3.Name is not null then l3.Name else '' end as PerformanceStatus,  
case when pat.PAT_ID is not null then pat.PAT_ID else '' end as PAT_ID,  
case when pat.ConcurRadTherapy is not null then pat.ConcurRadTherapy else '' end as ConcurRadTherapy,  
case when pat.AssignedByUser is not null then pat.AssignedByUser else '' end as AssignedByUser,  
case when pat.ApprovedByUser is not null then pat.ApprovedByUser else '' end as ApprovedByUser  
FROM Patient_Assigned_Templates pat  
INNER JOIN Master_Template mt ON mt.Template_ID = pat.Template_ID  
INNER JOIN LookUp l1 ON l1.Lookup_ID = mt.Regimen_ID  
INNER JOIN LookUp l3 ON l3.Lookup_ID = pat.Perf_Status_ID  
LEFT OUTER JOIN LookUp l2 ON l2.Name = convert(nvarchar(max),mt.Regimen_ID)  
WHERE pat.Patient_ID = '$id'";
        }
        
        return $this->query($query);
    }



    function getVitalsFromVistA_AsArray($DFN) {
        // get Vitals from VistA
        $controller = 'mymdwscontroller';
        $MyMDWSController = new $controller('mymdws', 'mymdws', null);
        $VistAVitals = $MyMDWSController->getVitalsFromVistA($DFN);
        $VistAPatientInfo = $MyMDWSController->getPatientInfoFromVistA($DFN);
        // error_log("VistA Vitals - " . json_encode($VistAVitals));
        $vitals = array();
        foreach($VistAVitals as $aVital) {
            // error_log("A Vital = " . json_encode($aVital));
            $aDate = $aVital->date;


$date = new DateTime($aDate, new DateTimeZone('UTC'));
$aDate = $date->format('m/d/Y H:i:s');


            // $aDate = date("Y-m-d H:i:s", $aDate);
            // error_log($aDate);
            // error_log($aVital->description->desc);
            if (!isset($vitals[$aDate])) {
                $vitals[$aDate] = array();
                $vitals[$aDate]["Age"] = $VistAPatientInfo->age;
                $vitals[$aDate]["Gender"] = $VistAPatientInfo->gender;
            }
            switch ($aVital->type) {
                case "BP":
                    $vitals[$aDate]["BP"] = $aVital->value;
                    break;
                case "PN":
                    $vitals[$aDate]["Pain"] = $aVital->value;
                    break;
                default:
                    if (array_key_exists("description", $aVital)) {
                        $vitals[$aDate][$aVital->description->desc] = $aVital->value;
                    }
                    else {
                        $vitals[$aDate][$aVital->type] = $aVital->value;
                    }
            }
        }
        // error_log("Vitals as array = " . json_encode($vitals));
        return $vitals;
    }










    function getMeasurements ($id) {
        $query = "SELECT Height as height, Weight as weight,Blood_Pressure as bp,Weight_Formula as weightFormula, " .
                     "BSA_Method as bsaMethod, BSA as bsa,BSA_Weight as bsaWeight,CONVERT(VARCHAR(10), Date_Taken, 101) as dateTaken, " .
                     "Temperature, Pulse, Respiration, Pain, OxygenationLevel as spo2level " .
                     "FROM Patient_History ph " . "WHERE ph.Patient_ID = '" . $id .
                     "' " . "ORDER BY Date_Taken DESC";
        return $this->query($query);
    }


    function _CkMeasurementMatch ($vitals2Return, $Key) {
        foreach($vitals2Return as &$aTVital) {
            if ($aTVital["Date"] == $Key) {
                return true;
            }
        }
        return false;
    }

    function getMostRecentHeightWeight($Vitals) {
        $len = count($Vitals);
        $h = "";
        $w = "";
        for ($i = 0; $i < $len; $i++) {
            if ("" == $h && "" !== $Vitals[$i]["Height"]) {
                $h = $Vitals[$i]["Height"];
            }
            if ("" == $w && "" !== $Vitals[$i]["Weight"]) {
                $w = $Vitals[$i]["Weight"];
            }
            if ("" !== $h && "" !== $w) {
                for ($j = 0; $j < $i; $j++) {
                    if ("" == $Vitals[$j]["Weight"]) {
                        $Vitals[$j]["Weight"] = $w;
                    }
                    if ("" == $Vitals[$j]["Height"]) {
                        $Vitals[$j]["Height"] = $h;
                    }
                }
                return;
            }
        }
    }





    function getMeasurements_v1 ($id, $dateTaken)
    {
        $DFN = $id;
        if (is_int($id)) {
            $query = "SELECT Patient_ID, DFN from Patient where DFN = '$id'";
        }
        else {
            $query = "SELECT Patient_ID, DFN from Patient where Patient_ID = '$id'";
        }
        $patientId = $this->query($query);
        if (array_key_exists("error", $patientId)) {
            $errMsgList[] = "Error retrieving patient measurements" . $patientId["error"];
            return $patientId;
        }




        $id = $patientId[0]['Patient_ID'];
        $DFN = $patientId[0]['DFN'];

// error_log("getMeasurements_v1 - (get Vitals) From VistA - DFN - $DFN; ID - $id");


        $VistAVitals = $this->getVitalsFromVistA_AsArray($DFN);
// error_log("getMeasurements_v1 - (get Vitals) From VistA" . json_encode($VistAVitals));
// error_log("getMeasurements_v1 - Add Age and Gender from VistA to Vitals from SQL");
        reset($VistAVitals);
        $firstKey = key($VistAVitals);
        $age = $VistAVitals[$firstKey]["Age"];
        $gender = $VistAVitals[$firstKey]["Gender"];


        $baseQuery = "SELECT 
            ph.Height as Height,
            ph.Weight as Weight,
            BP = CAST(Systolic as varchar(5)) + '/' + CAST(Diastolic as varchar(5)), 
            Weight_Formula as WeightFormula, 
            BSA_Method as BSA_Method, 
            BSA,
            BSA_Weight,
            CONVERT(VARCHAR(10), Date_Taken, 101) + ' ' + CONVERT(VARCHAR(8), Date_Taken, 108) as DateTaken,
            CONVERT(VARCHAR(10), Date_Taken, 101) as DateTaken2, 
            Temperature, 
            TemperatureLocation, 
            Pulse, 
            Respiration, 
            Pain, 
            OxygenationLevel as SPO2, 
            Cycle, 
            Admin_Day as Day, 
            CASE WHEN ph.Performance_ID is null then 'No Change' else l4.Description END as PS, 
            CASE WHEN ph.Performance_ID is null then 'N/C' else l4.Name END as PSID, 
            Age = '', 
            Gender = ''
            FROM Patient_History ph 
            INNER JOIN Patient p ON p.Patient_ID = ph.Patient_ID 
            LEFT JOIN LookUp l4 ON l4.Lookup_ID = ph.Performance_ID 
            WHERE ph.Patient_ID = '$id' and Date_Taken is not null";

        if (null == $dateTaken) {
            $query = $baseQuery . " ORDER BY Date_Taken DESC";
        } else {
            $query = $baseQuery . " AND CONVERT(VARCHAR(10), Date_Taken, 105) = '$dateTaken' ORDER BY Date_Taken DESC";
        }
        // error_log("getMeasurements_v1 - $query");


        $retVal = $this->query($query);
        // error_log("getMeasurements_v1 - Vitals from SQL - " . json_encode($retVal));

        $vitals2Return = array();

        // Sort the return set based on Date Taken in Descending Order so most recent date first
        if (null != $retVal && !array_key_exists('error', $retVal)) {
            /// Add SQL Data to log...
            // error_log("Vitals from SQL");
            $c = 0;
            foreach($retVal as &$aVital) {
                $c++;
                $zDate = $aVital["DateTaken"];
                $date = new DateTime( $zDate );
                $aDateLocal = $date->format('m/d/Y');
                $aDate = strtotime($zDate);
                if (!array_key_exists($aDate, $vitals2Return)) {    // Key contains time and date.
                    $vitals2Return[$aDate] = array();
                    $vitals2Return[$aDate] = $aVital;
                    $vitals2Return[$aDate]["DateTaken"] = $zDate;
                    $vitals2Return[$aDate]["Date"] = $aDateLocal;
                    $vitals2Return[$aDate]["Age"] = $age;
                    $vitals2Return[$aDate]["Gender"] = $gender;
                    $vitals2Return[$aDate]["Src"] = "SQL";
                }
                else {
                    // error_log("Date Matching Existing Data - ");
                    // error_log("Old - " . json_encode($vitals2Return[$aDate]));
                    // error_log("New - " . json_encode($aVital));
                }
                // error_log( "SQL Entry = " . json_encode($vitals2Return[$aDate]));
            }
            $c = 0;

            $UTC = new DateTimeZone("UTC");
            $newTZ = new DateTimeZone(date_default_timezone_get ( ));

            foreach($VistAVitals as $aDate => &$aVital) {
                $date = new DateTime( $aDate, $UTC );
                $date->setTimezone( $newTZ );
                $aDateLocal = $date->format('m/d/Y h:m:s');
                $DateOnly = $date->format('m/d/Y');
                $c++;
                $zDate = strtotime($aDateLocal);
                if (!$this->_CkMeasurementMatch($vitals2Return, $DateOnly)) {
                    $vitals2Return[$zDate] = array();
                    $vitals2Return[$zDate] = $aVital;
                    $vitals2Return[$zDate]["DateTaken"] = $aDateLocal;
                    $vitals2Return[$zDate]["Date"] = $DateOnly;
                    $vitals2Return[$zDate]["TimeStampUTZ"] = $aDate;
                    $vitals2Return[$zDate]["Age"] = $age;
                    $vitals2Return[$zDate]["Gender"] = $gender;
                    $vitals2Return[$zDate]["Src"] = "VistA";
                    // error_log( "VistA Entry = " . json_encode($vitals2Return[$zDate]));
                }
                else {
                    // error_log( "VistA Entry = No need to add Vista data to SQL Record - " . json_encode($aVital));
                }
            }
        }
        else if (null == $retVal) {
            foreach($VistAVitals as $aDate => &$aVital) {
                $zDate = strtotime($aDate);
                // error_log("Vitals from VistA, Adding to NULL Array - " . json_encode($aVital));
                $aVital["DateTaken"] = $aDate;
                $aVital["Src"] = "VistA";
                $vitals2Return[$zDate] = $aVital;
            }
        }

        $retVal1 = array();
        foreach($vitals2Return as $aDate => &$aVital) {
            // error_log("Vitals Being Returned, Key = $aDate; " . $aVital["DateTaken"] . "; " . $aVital["Src"] . " Data = " . json_encode($aVital));
            $retVal1[] = $aVital;
        }
        $this->getMostRecentHeightWeight(&$retVal1);


        return $retVal1;
    }

    function saveVitals ($form_data, $patientId) {

        // error_log("Patient.Model.saveVitals - " . json_encode($form_data));
        if (empty($patientId)) {
            
            if (isset($form_data->{'patientId'})) {
                $patientId = $form_data->{'patientId'};
            } else if (isset($form_data->{'PatientID'})) { // MWB - 6/21/2012
                                                    // The JS Model
                                                    // calls for the
                                                    // field name to be
                                                    // 'PatientID' not
                                                    // 'patientId',
                $patientId = $form_data->{'PatientID'}; // but not sure how
                                                            // the 'patientId'
                                                            // field gets set so
                                                            // making sure to
                                                            // check both...
            } else {
                $retVal = array();
                $retVal['apperror'] = "Field name ---patientId--- not provided.";
                return $retVal;
            }
        }


        if (empty($dateTaken)) {
            $dateTaken = $this->getCurrentDate();
        }
        $objDateTime = new DateTime('NOW');
        $observed = $objDateTime->format(DateTime::ISO8601);

                $nodevista = new NodeVista();
                $VistATime = $nodevista->get("current/date");

                $vts = json_decode($VistATime);
                $vtsDateStr = $vts->{'date'};
// error_log("VistA Time = $VistATime" . $vts->{'date'} . " DateTaken from SQL - " . $dateTaken);


	  $theCenturyMultiplier = substr($vtsDateStr, 0, 1);
	  $theCentury = 1700 + (100 * $theCenturyMultiplier);
	  $theYear = substr($vtsDateStr, 1, 2);
	  $y = intval($theCentury, 10) + intval($theYear, 10);
	  $theMonth = substr($vtsDateStr, 3, 2);
	  $theDay = substr($vtsDateStr, 5, 2);
	  $theHr = intval(substr($vtsDateStr, 8, 2), 10);
	  $AmPm = "";
	  if ($theHr > 12) {
		  $theHr = $theHr - 12;
		  $AmPm = "PM";
	  }
      if ($theHr < 10) {
          $theHr = "0$theHr";
      }
	  $theMin = substr($vtsDateStr, 10, 2);
	  $theDateTimeStr = "$theMonth/$theDay/$y $theHr:$theMin $AmPm";

// error_log("VistA Time = $VistATime, " . $vts->{'date'} . " - $theDateTimeStr");

$theDateTime = new DateTime($theDateTimeStr);

$observed = $theDateTime->format(DateTime::ISO8601);

$dateTaken = date_format($theDateTime, 'Y-m-d H:i:s');


// error_log("VistA Time =  $VistATime, " . $vts->{'date'} . " - $theDateTimeStr - Observed = $observed"); //  DateTaken from SQL - $dateTaken");


        if (isset($form_data->{'OEMRecordID'})) {
            $oemRecordId = $form_data->{'OEMRecordID'};
        } else {
            $oemRecordId = null;
        }


        $errMsgList = array();
        $nodevista = new NodeVista();
        $systolic = $form_data->{'Systolic'};
        $diastolic = $form_data->{'Diastolic'};
        
        if (empty($form_data->{'BP'})) {
            $bp = $systolic . "/" . $diastolic;
        } else {
            $bp = $form_data->{'BP'};
        }
        
        $bp1=preg_replace('/\s+/', '', $bp);
        if ($bp && $bp1 !== "" && $bp1 !== "/") {
            $VitalObj = array('type' => "BP", 'value' => $bp1, 'provider' => $_SESSION['UserDUZ']);
            $PatientData = array('patient' => $form_data->{'DFN'}, 'location' => $_SESSION[ 'sitelist' ], 'observed_date_time' => $observed, 'vital' => $VitalObj);
            $PatientData = json_encode($PatientData);
// error_log("Posting Vital to VistA - patient/vital/add - $PatientData");
            $postRet = $nodevista->post("patient/vital/add" , $PatientData);
            $eRet1 = json_decode( $postRet, true );
            if (array_key_exists("error", $eRet1)) {
                $errMsgList[] = "Error saving BP to VistA - " . $eRet1["error"];
                $bp = "";
            }
        }


        $height = $form_data->{'Height'};
        if ($height && $height !== "") {
            $VitalObj = array('type' => "HT", 'value' => $height, 'provider' => $_SESSION['UserDUZ']);
            $PatientData = array('patient' => $form_data->{'DFN'}, 'location' => $_SESSION[ 'sitelist' ], 'observed_date_time' => $observed, 'vital' => $VitalObj);
            $PatientData = json_encode($PatientData);
            $postRet = $nodevista->post("patient/vital/add" , $PatientData);

            $eRet1 = json_decode( $postRet, true );
            if (array_key_exists("error", $eRet1)) {
                $errMsgList[] = "Error saving Height to VistA - " . $eRet1["error"];
                $height = "";
            }
        }

        $weight = $form_data->{'Weight'};
        if ($weight && $weight !== "") {
            $VitalObj = array('type' => "WT", 'value' => $weight, 'provider' => $_SESSION['UserDUZ']);
            $PatientData = array('patient' => $form_data->{'DFN'}, 'location' => $_SESSION[ 'sitelist' ], 'observed_date_time' => $observed, 'vital' => $VitalObj);
            $PatientData = json_encode($PatientData);
            $postRet = $nodevista->post("patient/vital/add" , $PatientData);

            $eRet1 = json_decode( $postRet, true );
            if (array_key_exists("error", $eRet1)) {
                $errMsgList[] = "Error saving Weight to VistA - " . $eRet1["error"];
                $weight = "";
            }
        }


        $temp = $form_data->{'Temperature'};
        $tempLoc = $form_data->{'TemperatureLocation'};
        if ($temp && $temp !== "") {
            $VitalObj = array('type' => "T", 'value' => $temp, 'provider' => $_SESSION['UserDUZ']);
            $PatientData = array('patient' => $form_data->{'DFN'}, 'location' => $_SESSION[ 'sitelist' ], 'observed_date_time' => $observed, 'vital' => $VitalObj);
            $PatientData = json_encode($PatientData);
            $postRet = $nodevista->post("patient/vital/add" , $PatientData);

            $eRet1 = json_decode( $postRet, true );
            if (array_key_exists("error", $eRet1)) {
                $errMsgList[] = "Error saving Temperature to VistA - " . $eRet1["error"];
                $temp = "";
            }
        }

        $pulse = $form_data->{'Pulse'};
        if ($pulse && $pulse !== "") {
            $VitalObj = array('type' => "P", 'value' => $pulse, 'provider' => $_SESSION['UserDUZ']);
            $PatientData = array('patient' => $form_data->{'DFN'}, 'location' => $_SESSION[ 'sitelist' ], 'observed_date_time' => $observed, 'vital' => $VitalObj);
            $PatientData = json_encode($PatientData);
            $postRet = $nodevista->post("patient/vital/add" , $PatientData);

            $eRet1 = json_decode( $postRet, true );
            if (array_key_exists("error", $eRet1)) {
                $errMsgList[] = "Error saving Pulse to VistA - " . $eRet1["error"];
                $pulse = "";
            }
        }

        $resp = $form_data->{'Respiration'};
        if ($resp && $resp !== "") {
            $VitalObj = array('type' => "R", 'value' => $resp, 'provider' => $_SESSION['UserDUZ']);
            $PatientData = array('patient' => $form_data->{'DFN'}, 'location' => $_SESSION[ 'sitelist' ], 'observed_date_time' => $observed, 'vital' => $VitalObj);
            $PatientData = json_encode($PatientData);
            $postRet = $nodevista->post("patient/vital/add" , $PatientData);

            $eRet1 = json_decode( $postRet, true );
            if (array_key_exists("error", $eRet1)) {
                $errMsgList[] = "Error saving Respiration to VistA - " . $eRet1["error"];
                $resp = "";
            }
        }

        $pain = $form_data->{'Pain'};
        if ($pain && $pain !== "") {
            $VitalObj = array('type' => "PN", 'value' => $pain, 'provider' => $_SESSION['UserDUZ']);
            $PatientData = array('patient' => $form_data->{'DFN'}, 'location' => $_SESSION[ 'sitelist' ], 'observed_date_time' => $observed, 'vital' => $VitalObj);
            $PatientData = json_encode($PatientData);
            $postRet = $nodevista->post("patient/vital/add" , $PatientData);

            $eRet1 = json_decode( $postRet, true );
            if (array_key_exists("error", $eRet1)) {
                $errMsgList[] = "Error saving Pain to VistA - " . $eRet1["error"];
                $pain = "";
            }
        }


        $spo2 = $form_data->{'SPO2'};
        if ($spo2 && $spo2 !== "") {
            $spo2 = strval($spo2);

/**********
            $VitalObj = array('type' => "PO2", 'value' => $spo2, 'provider' => $_SESSION['UserDUZ']);
            $PatientData = array('patient' => $form_data->{'DFN'}, 'location' => $_SESSION[ 'sitelist' ], 'observed_date_time' => $observed, 'vital' => $VitalObj);
            $PatientData = json_encode($PatientData);
// error_log("Saving Pulse Oximetry - Data = $PatientData");
            $postRet = $nodevista->post("patient/vital/add" , $PatientData);
            $eRet1 = json_decode( $postRet, true );
            if (array_key_exists("error", $eRet1)) {
                $errMsgList[] = "Error saving SPO2 to VistA - " . $eRet1["error"];
                $spo2 = "";
            }
************/
        }

        
        if (count($errMsgList) > 0) {
            $errors = implode("\n\r", $errMsgList);
            $AppErr = array();
            $AppErr['apperror'] = $errors;
            return $AppErr;
        }


// error_log("No VistA Errors...");

        $bsa = $form_data->{'BSA'};
        $bsaMethod = $form_data->{'BSA_Method'};
        $weightFormula = $form_data->{'WeightFormula'};
        $bsaWeight = $form_data->{'BSA_Weight'};
        
        $templateId = $this->getTemplateIdByPatientID($patientId);
        if (null != $templateId && array_key_exists('error', $templateId)) {
            return $templateId;
        } else if (! empty($templateId)) {
            $templateId = $templateId[0]['id'];
        } else {
            $templateId = null;
        }
        
        $PS_ID = null;
        if (isset($form_data->{'PS_ID'})) {
            $PS_ID = $form_data->{'PS_ID'};
        }
        
        /*
         * Not sure if Performance ID is important when saving Vitals. Seems to
         * make sense to save Performance Status if it was set before the Vitals
         * were taken. In other words the template was applied and the
         * Performance Status was set before the vitals were taken. Also use BSA
         * values for WeightFormula and BSA Method if the Start Date of the
         * Template is after the Date Taken
         */
        $query = "SELECT Perf_Status_ID as id,BSA_Method as bsaMethod,Weight_Formula as weightFormula 
                 FROM Patient_Assigned_Templates where Is_Active = 1 and Patient_ID = '$patientId' AND Date_Started <= '$dateTaken'";
        
        $record = $this->query($query);
        if (null != $record && array_key_exists('error', $record)) {
// error_log("getting Performance Status Error - $query");
            return $record;
        } else if (count($record) > 0) {
            if (null === $PS_ID) {
                // $performanceId = $record[0]['id'];
                $performanceId = null;
            } else {
                $performanceId = $PS_ID;
            }
            if ("" == $bsaMethod) {
                $bsaMethod = $record[0]['bsaMethod'];
            }
            if ("" == $weightFormula) {
                $weightFormula = $record[0]['weightFormula'];
            }
        }

// error_log("Performance Status Saved");
        
        if (null == $oemRecordId) {
            $ob1 = explode("T", $observed);
            $time = explode("-", $ob1[1]);
            $ob2 = $ob1[0] . " ". $time[0];
// error_log("SQL Time ($ob2) from VistA time ($observed)");
$observed = $ob2;
            if (! empty($templateId)) {
                $query = "INSERT INTO Patient_History(Patient_ID,Height,Weight,Blood_Pressure,Systolic,Diastolic,BSA,Temperature,TemperatureLocation,Date_Taken, " .
                         "Template_ID, Pulse, Respiration, Pain, OxygenationLevel,BSA_Method,Weight_Formula,BSA_Weight,Performance_ID) values(" .
                         "'" . $patientId . "','" . $height . "','" . $weight .
                         "','" . $bp . "','" . $systolic . "','" . $diastolic .
                         "','" . $bsa . "','$temp','$tempLoc','" . $observed . "'," .
                         "'" . $templateId . "','" . $pulse . "','" . $resp .
                         "','" . $pain . "','" . $spo2 . "'," . "'" . $bsaMethod .
                         "','" . $weightFormula . "','" . $bsaWeight . "',";
            } else {
                $query = "INSERT INTO Patient_History(Patient_ID,Height,Weight,Blood_Pressure,Systolic,Diastolic,BSA,Temperature,TemperatureLocation,Date_Taken, " .
                         "Pulse, Respiration, Pain, OxygenationLevel,BSA_Method,Weight_Formula,BSA_Weight,Performance_ID) values(" .
                         "'" . $patientId . "','" . $height . "','" . $weight .
                         "','" . $bp . "','" . $systolic . "','" . $diastolic .
                         "','" . $bsa . "','$temp','$tempLoc','" . $observed . "'," .
                         "'" . $pulse . "','" . $resp . "','" . $pain . "','" .
                         $spo2 . "'," . "'" . $bsaMethod . "','" . $weightFormula .
                         "','" . $bsaWeight . "',";
            }
        } else {
            if (! empty($templateId)) {
                $query = "INSERT INTO Patient_History(Patient_ID,Height,Weight,Blood_Pressure,Systolic,Diastolic,BSA,Temperature,TemperatureLocation,Date_Taken, " .
                         "Template_ID, OEM_ID, Pulse, Respiration, Pain, OxygenationLevel,BSA_Method,Weight_Formula,BSA_Weight,Performance_ID) values(" .
                         "'" . $patientId . "','" . $height . "','" . $weight .
                         "','" . $bp . "','" . $systolic . "','" . $diastolic .
                         "','" . $bsa . "','$temp','$tempLoc','" . $observed . "'," .
                         "'" . $templateId . "','" . $oemRecordId . "','" .
                         $pulse . "','" . $resp . "','" . $pain . "','" . $spo2 .
                         "'," . "'" . $bsaMethod . "','" . $weightFormula . "','" .
                         $bsaWeight . "',";
            } else {
                $query = "INSERT INTO Patient_History(Patient_ID,Height,Weight,Blood_Pressure,Systolic,Diastolic,BSA,Temperature,Date_Taken, " .
                         "Pulse, Respiration, Pain, OxygenationLevel,BSA_Method,Weight_Formula,BSA_Weight,Performance_ID) values(" .
                         "'" . $patientId . "','" . $height . "','" . $weight .
                         "','" . $bp . "','" . $systolic . "','" . $diastolic .
                         "','" . $bsa . "','$temp','$tempLoc','" . $observed . "'," .
                         "'" . $pulse . "','" . $resp . "','" . $pain . "','" .
                         $spo2 . "'," . "'" . $bsaMethod . "','" . $weightFormula .
                         "','" . $bsaWeight . "',";
            }
        }

        (empty($performanceId)) ? $query .= "null)" : $query .= "'$performanceId')";

// error_log("Patient.Model.saveVitals - $query");

        $result = $this->query($query);
        
        if ($result) {
            return $result;
        }
        
        if (! empty($performanceId)) {
            $query = "
                UPDATE Patient_Assigned_Templates SET
                    Perf_Status_ID = '$performanceId'
                WHERE Patient_ID = '$patientId'
                    AND Is_Active = 1
            ";
            
            return $this->query($query);
        }
    }

    function updateOEMRecord ($form_data)
    {

error_log("Patient.Model.updateOEMRecord - Form Data = " . json_encode($form_data));
/**
 *	Get and check all the data needed 
 *
 **/
        $templateid = $form_data->{'TemplateID'};
        $oemrecordid = $form_data->{'OEMRecordID'};
        $order_id = $form_data->{'Order_ID'};
        $therapyid = $form_data->{'TherapyID'};
        $therapytype = $form_data->{'TherapyType'};
        $instructions = $form_data->{'Instructions'};
        $Status = $form_data->{'Status'};
        $admintime = $form_data->{'AdminTime'};
        $medid = $form_data->{'MedID'};
        $med = $form_data->{'Med'};
        $dose = $form_data->{'Dose'};
        $bsadose = $form_data->{'BSA_Dose'};

        $units = $form_data->{'Units'};
        $infusionmethod = $form_data->{'InfusionMethod'};	// This is the VistA IEN for the Drug Route selected
        $fluidtype = $this->escapeString($form_data->{'FluidType'});
        $fluidvol = $form_data->{'FluidVol'};
        $flowrate = $form_data->{'FlowRate'};
        $infusiontime = $form_data->{'InfusionTime'};

        $id = $form_data->{'id'};



        $dose2 = $form_data->{'Dose'};
        $bsadose2 = $form_data->{'BSA_Dose'};
        $units2 = $form_data->{'Units'};
        $infusionmethod2 = $form_data->{'InfusionMethod'};
        $fluidtype2 = $this->escapeString($form_data->{'FluidType'});
        $fluidvol2 = $form_data->{'FluidVol'};
        $flowrate2 = $form_data->{'FlowRate'};
        $infusiontime2 = "";
        if (property_exists($form_data, 'InfusionTime2')) {
            $infusiontime2 = $form_data->{'InfusionTime2'};
        }

        $Reason = $form_data->{'Reason'};
        if (0 == intval($Reason)) {
            if ("" != $Reason) {
                $retVal = $this->convertReason2ID($Reason);
                $Reason = 0;
                if (null != $retVal) {
                    $Reason = $retVal[0]["Reason"];
                }
            }
        }

/**
{
$templateid    "TemplateID": "63008D40-88C2-4D9B-B87E-70EA48A17627",
$oemrecordid    "OEMRecordID": "EE250B8C-03E2-42F7-BF00-5B33D08ED718",
$order_id    "Order_ID": "E9831A22-E58F-4C23-B5A8-1FDBDD1D3DC0",
$therapyid    "TherapyID": "",
$therapytype    "TherapyType": "Pre",
$instructions    "Instructions": "Patient to take 4mg by mouth prior to chemotherapy",
$Status    "Status": "",
$admintime    "AdminTime": "",
$medid    "MedID": "280C5615-A204-E511-9B8C-000C2935B86F",
$med    "Med": "DECADRON     (DEXAMETHASONE TAB )",
$Reason    "Reason": "Change Administration Time",
$dose    "Dose": "4000",
$bsadose    "BSA_Dose": "",
$units    "Units": "mg",
$infusionmethod    "InfusionMethod": "ORAL (BY MOUTH) : 12",
$fluidtype    "FluidType": "",
$fluidvol    "FluidVol": "0",
$flowrate    "FlowRate": "",
$infusiontime    "InfusionTime": "",
    "id": null
}
**/
        
        $retVal = array();
        
        if (empty($therapytype)) {
            $retVal['apperror'] = "Therapy Type not provided.";
            return $retVal;
        }

        if (empty($therapyid)) {
            if ("Therapy" === $therapytype) {
                $q = "select Patient_Regimen_ID as Therapy_ID from Template_Regimen where Order_ID = '$order_id'";
            }
            else {
                $q = "select MH_ID as Therapy_ID from Medication_Hydration where Order_ID = '$order_id'";
            }
// error_log("Therapy ID not provided. Looking it up via $q");
            $retVal = $this->query($q);
            if (null != $retVal && array_key_exists('error', $retVal)) {
                $retVal['apperror'] = "Therapy ID not provided.";
// error_log(sprintf("%s; %d; %s; \n %s", __FILE__, __LINE__, $retVal['apperror'], $q));
                return $retVal;
            }
			$therapyid = $retVal[0]["Therapy_ID"];
// error_log(sprintf("%s; %d; GOT Therapy ID - $therapyid; \n %s", __FILE__, __LINE__, $q));
        }

        if (empty($oemrecordid)) {
            $retVal['apperror'] = "OEM Record ID not provided.";
// error_log(sprintf("%s; %d; %s", __FILE__, __LINE__, $retVal['apperror']));
            return $retVal;
        }
        
        if (empty($templateid)) {
            $retVal['apperror'] = "Template ID not provided.";
// error_log(sprintf("%s; %d; %s", __FILE__, __LINE__, $retVal['apperror']));
            return $retVal;
        }
        
        if (empty($med)) {
            $retVal['apperror'] = "Med not provided.";
// error_log(sprintf("%s; %d; %s", __FILE__, __LINE__, $retVal['apperror']));
            return $retVal;
        }
        
        if (empty($admintime)) {
            $admintime = '00:00:00';
        }

// error_log("All input validations passed...");


		$query = "select Lookup_Type_ID from LookUp where Lookup_ID = '$medid'";
		$medIEN = $this->query($query);
        if (null != $medIEN && array_key_exists('error', $medIEN)) {
// error_log(sprintf("%s; %d; %s", __FILE__, __LINE__, json_encode($medIEN)));
            return $medIEN;
        }
		$medIEN = $medIEN[0]["Lookup_Type_ID"];


// error_log("Getting additional data via lookups...");

// error_log("Patient.Model.updateOEMRecord - 1 MedID = $medid");
/****************** already have MedID passed as part of the form... **/
		$lookup = new LookUp();
        $info = $lookup->getLookupInfoById($medid);
// error_log("Patient.Model.updateOEMRecord - 2 getLookupInfoById($medid) - " . json_encode($info));
        if (null != $info && array_key_exists('error', $info)) {
            return $info;
        }

        if (array_key_exists ( "id" , $info[0] )) {
            $medid = $info[0]['id'];
        }
        else {
            if ($med != $info[0]['Name']) {
                $record = $lookup->getLookupIdByNameAndType($med, 2);
                $medid = $record[0]['id'];
// error_log("Patient.Model.updateOEMRecord - 3 getLookupIdByNameAndType($med) - " . json_encode($record));
            }
        }
 /*******************/
// error_log("Patient.Model.updateOEMRecord - 4 MedID = $medid");

/**
 *	Update Template_Regimen (for Therapy meds) or Medication_Hydration and MH_Infusion (for Pre/Post therapy meds)
 *
 **/
// error_log("Patient.Model.updateOEMRecord - 5 $therapytype - $infusionmethod");
        if ('Therapy' === $therapytype) {
			/* ------------ We're already passed the IEN of the Route of infusion ----------------
			// Magic # "12" is for the Regimen Route Type
            $infusionTypeid = $lookup->getLookupIdByNameAndType($infusionmethod, 12);
            if ($infusionTypeid) {
                $infusionTypeid = $infusionTypeid[0]["id"];
            } else {
                $infusionTypeid = null;
            }
            
            if (null == $infusionTypeid) {
error_log("Patient.Model.updateOEMRecord - infusionTypeid is NULL");
                $retVal = array();
                $retVal['error'] = "Insert int MH_ID for $type Therapy failed. The Route could not be determined.";
                return $retVal;
            }
			------------ We're already passed the IEN of the Route of infusion ---------------- */




            // Magic # "11" is for the Medication Unit Measurement
            $unitid = $lookup->getLookupIdByNameAndType($units, 11);
            if ($unitid) {
                $unitid = $unitid[0]["id"];
            } else {
                $unitid = null;
            }
            
            if (null == $unitid) {
// error_log("Patient.Model.updateOEMRecord - unitid is NULL");
                $retVal = array();
                $retVal['error'] = "Insert int MH_ID for $type Therapy failed. The unit id could not be determined.";
                return $retVal;
            }

			// Route_ID ='$infusionTypeid',		No longer use GUID so need to update the VistA_Route field
            $query = "Update Template_Regimen 
            set Drug_ID = '$medid',
            Admin_Time ='$admintime', 
            Instructions ='$instructions', 
            Status = '$Status',
            VistA_RouteInfo = '$infusionmethod',
            Regimen_Dose_Unit_ID ='$unitid', 
            Regimen_Dose ='$dose', 
            Flow_Rate ='$flowrate', 
            Fluid_Type ='$fluidtype', 
            Fluid_Vol ='$fluidvol', 
            BSA_Dose = '$bsadose', 
            Infusion_Time = '$infusiontime',
            Reason = '$Reason'
            where Patient_Regimen_ID = '$therapyid'";

error_log("Patient.Model.updateOEMRecord - Therapy - $query" );


            $retVal = $this->query($query);
            if (null != $retVal && array_key_exists('error', $retVal)) {
                return $retVal;
            }
        }
        else if ('Pre' === $therapytype || 'Post' === $therapytype) {
            $query = "select * from MH_Infusion where MH_ID = '$therapyid'";
error_log("Patient.Model.updateOEMRecord - Pre/Post - $query" );

            $infusionRecord = $this->query($query);
            if (null != $infusionRecord && array_key_exists('error', $infusionRecord)) {
                return $infusionRecord;
            }
            
            $query = "Update 
                Medication_Hydration set Drug_ID = '$medid',
                Admin_Time ='$admintime', 
                Description ='$instructions',
                Status = '$Status',
                Reason = '$Reason'
                where MH_ID = '$therapyid'";
error_log("Patient.Model.updateOEMRecord - Update - $query" );

            $retVal = $this->query($query);
            if (null != $retVal && array_key_exists('error', $retVal)) {
                return $retVal;
            }

// error_log(sprintf("%s %d %s, = Update - $query", __FILE__, __LINE__, __FUNCTION__));

// error_log(sprintf("%s %d %s, = Walking Infusion Data; %d", __FILE__, __LINE__, __FUNCTION__, count($infusionRecord)));

            for ($index = 0; $index < count($infusionRecord); $index ++) {
                if (1 == $index) {
                    $infusionmethod = $infusionmethod2;
                    $units = $units2;
                    $dose = $dose2;
                    $bsadose = $bsadose2;
                    $fluidtype = $fluidtype2;
                    $flowrate = $flowrate2;
                    $fluidvol = $fluidvol2;
                    $infusiontime = $infusiontime2;
                }


				/* ------------ We're already passed the IEN of the Route of infusion ----------------
                $infusionTypeid = $lookup->getLookupIdByNameAndType($infusionmethod, 12);
                if ($infusionTypeid) {
                    $infusionTypeid = $infusionTypeid[0]["id"];
                } else {
                    $infusionTypeid = null;
                }
                if (null == $infusionTypeid) {
                    $retVal = array();
                    $retVal['error'] = "Insert into MH_ID for $therapytype Therapy failed. The Route could not be determined.";
error_log(sprintf("%s %d %s, = %s; $infusionmethod", __FILE__, __LINE__, __FUNCTION__, $retVal['error']));
                    return $retVal;
                }
				------------ We're already passed the IEN of the Route of infusion ---------------- */


                $unitid = $lookup->getLookupIdByNameAndType($units, 11);
                if ($unitid) {
                    $unitid = $unitid[0]["id"];
                } else {
                    $unitid = null;
                }
                if (null == $unitid) {
                    $retVal = array();
                    $retVal['error'] = "Insert int MH_ID for $therapytype Therapy failed. The unit id could not be determined.";
// error_log(sprintf("%s %d %s, = %s; $units", __FILE__, __LINE__, __FUNCTION__, $retVal['error']));
                    return $retVal;
                }


                // Infusion_Type_ID='$infusionTypeid',		No longer use GUID so need to update the VistA_Route field
                $query = "Update MH_Infusion 
                set Infusion_Amt = '$dose',
                BSA_DOSE ='$bsadose',
                Infusion_Unit_ID='$unitid',
                VistA_RouteInfo = '$infusionmethod',
                Fluid_Type='$fluidtype',
                Flow_Rate='$flowrate',
                Fluid_Vol='$fluidvol',
                Infusion_Time='$infusiontime'
                where Infusion_ID ='" .$infusionRecord[$index]['Infusion_ID'] . "'";

error_log("Patient.Model.updateOEMRecord - $therapytype - $query" );
                $retVal = $this->query($query);
                if (null != $retVal && array_key_exists('error', $retVal)) {
// error_log(sprintf("%s %d %s, = Update Failed", __FILE__, __LINE__, __FUNCTION__));
// error_log(json_encode($retVal));
					return $retVal;
                }
            }
// error_log(sprintf("%s %d %s, = Walking Infusion Data Complete", __FILE__, __LINE__, __FUNCTION__));
        }

// error_log("Patient.Model.updateOEMRecord - 6 Finishing Up...");

		$medName = $med . " : " . $medIEN;
/**
Cols are too small for Fluid Type and all other records are NULL
      FluidType = '$fluidtype',
      FluidVol = '$fluidvol',
      FlowRate = '$flowrate',

 **/
		$query = "UPDATE Order_Status
   SET 
      Drug_Name = '$medName',
      Drug_ID = '$medid',
      Amt = '$dose',
      Unit = '$units',
      Route = '$infusionmethod',
      flvol = '$fluidvol',
      infusion = '$infusiontime',
      bsaDose = '$bsadose'
 WHERE Order_ID = '$order_id'";

// error_log("Patient.Model.updateOEMRecord - Order_Status - $query" );
        $retVal = $this->query($query);
        if (null != $retVal && array_key_exists('error', $retVal)) {
            return $retVal;
        }




    }

    function addNewPatient ($patient, $SSN_ID, $GUID)
    {
        // error_log("addNewPatient() - Patient - $SSN_ID; $GUID; " . json_encode($patient));
//        error_log("addNewPatient() - localPID = " . $patient->localPid);
//        error_log("addNewPatient() - localPID = " . $patient['localPid']);

        $dfn = $patient['localPid'];
        $sqlcurDate = $this->getCurrentDate();
        $Performance_ID = '73DA9443-FF74-E111-B684-000C2935B86F';
        $query2 = "INSERT INTO Patient_History (Performance_ID,Patient_ID) values('$Performance_ID','$GUID')";
        $this->query($query2);

        $query  = "INSERT INTO Patient (Patient_ID,Date_Created,DFN) values('$GUID','$sqlcurDate','$dfn')";
// error_log("addNewPatient - $query");
        return $this->query($query);
    }

    function savePatient ($form_data)
    {
        $measurements = $form_data->{'Measurements'};
        
        $patientId = $form_data->{'id'};
        
        for ($index = 0; $index < count($measurements); $index ++) {
            
            $measurementData = $measurements[$index]->{'data'};
            
            $height = $measurementData->{'Height'};
            $weight = $measurementData->{'Weight'};
            $bp = $measurementData->{'BP'};
            $weightFormula = $measurementData->{'WeightFormula'};
            $bsaMethod = $measurementData->{'BSA_Method'};
            $bsa = $measurementData->{'BSA'};
            $dateTaken = $measurementData->{'DateTaken'};
            $bsaWeight = $measurementData->{'BSA_Weight'};
        }
        
        $query = "Select Patient_History_ID as id from Patient_History where Patient_ID = '" .
                 $patientId . "' and Date_Taken = '" . $dateTaken . "'";
        
        $retVal = $this->query($query);
        
        if (null != $retVal && array_key_exists('error', $retVal)) {
            return $retVal;
        } else if ($retVal) {
            $query = "Update Patient_History set Height = '" . $height .
                     "', Weight = '" . $weight . "', Blood_Pressure='" . $bp .
                     "', Weight_Formula ='" . $weightFormula .
                     "', BSA_Method = '" . $bsaMethod . "', BSA_Weight = '" .
                     $bsaWeight . "',BSA = '" . $bsa . "' " .
                     "where Date_Taken = '" . $dateTaken . "' and Patient_ID = '" .
                     $patientId . "'";
        } else {
            
            $lookup = new LookUp();
            
            if (DB_TYPE == 'sqlsrv' || DB_TYPE == 'mssql') {
                $query = "select Perf_Status_ID as id from Patient_Assigned_Templates where Is_Active = 1 and Patient_ID = '" .
                         $patientId . "'";
            } else if (DB_TYPE == 'mysql') {
                $query = "select Perf_Status_ID as id from Patient_Assigned_Templates where Is_Active = true and Patient_ID = '" .
                         $patientId . "'";
            }
            
            $performanceId = $this->query($query);
            if (null != $performanceId &&
                     array_key_exists('error', $performanceId)) {
                return $performanceId;
            }
            
            $query = "INSERT INTO Patient_History(Patient_ID,Height,Weight,Blood_Pressure,Weight_Formula,BSA_Method,BSA,BSA_Weight,Date_Taken, " .
                     "Performance_ID) values(" . "'" . $patientId . "','" .
                     $height . "','" . $weight . "','" . $bp . "','" .
                     $weightFormula . "','" . $bsaMethod . "','" . $bsa . "','" .
                     $bsaWeight . "','" . $dateTaken . "'," . "'" .
                     $performanceId[0]['id'] . "')";
        }
        
        $retVal = $this->query($query);
        
        if (null != $retVal && array_key_exists('error', $retVal)) {
            return $retVal;
        }
        
        $query = "Select Patient_History_ID as id from Patient_History where Patient_ID = '$patientId' and Date_Taken = '$dateTaken'";
        
        return $this->query($query);
    }

    function getTopLevelOEMRecords ($patientId, $id)
    {
        $query = "Select Regimen_ID as RegimenID from Master_Template where Template_ID = '$id'";
        
        $regimenId = $this->query($query);
        
        if (null != $regimenId && array_key_exists('error', $regimenId)) {
            return $regimenId;
        }
        
        if (DB_TYPE == 'sqlsrv' || DB_TYPE == 'mssql') {
            $query = "select Course_Number as CourseNum, Admin_Day as Day, CONVERT(VARCHAR(10), Admin_Date, 101) as AdminDate, Pre_MH_Instructions as PreTherapyInstr, " .
                     "Regimen_Instruction as TherapyInstr, Post_MH_Instructions as PostTherapyInstr, Template_ID as TemplateID " .
                     "from Master_Template " . "where Course_Number != 0 " .
                     "and Regimen_ID = '" . $regimenId[0]['RegimenID'] . "' " .
                     "and Patient_ID = '" . $patientId . "' " .
                     "order by Admin_Date";
        } else if (DB_TYPE == 'mysql') {
            $query = "select Course_Number as CourseNum, Admin_Day as Day, date_format(Admin_Date, '%m/%d/%Y') as AdminDate, Pre_MH_Instructions as PreTherapyInstr, " .
                     "Regimen_Instruction as TherapyInstr, Post_MH_Instructions as PostTherapyInstr, Template_ID as TemplateID " .
                     "from Master_Template " . "where Course_Number != 0 " .
                     "and Regimen_ID = '" . $regimenId[0]['RegimenID'] . "' " .
                     "and Patient_ID = '" . $patientId . "' " .
                     "order by Admin_Date";
        }

        return $this->query($query);
    }

    function getTopLevelOEMRecordsNextThreeDays ($patientId, $id)
    {
        $today = date('m/d/Y');
        $EndDate = mktime(0, 0, 0, date("m"), date("d") + 3, date("Y"));
        // $EndDate = mktime(0,0,0,date("m"),date("d")+2,date("Y"));
        $EndDateSearch = date("m/d/Y", $EndDate);
        
        $query = "Select Regimen_ID as RegimenID from Master_Template where Template_ID = '$id'";
// error_log("Patient.Model.getTopLevelOEMRecordsNextThreeDays - PatientID = $patientId; TemplateID = $id");
// error_log($query);


        $regimenId = $this->query($query);
        
        if (null != $regimenId && array_key_exists('error', $regimenId)) {
            return $regimenId;
        }
        
        $query = "
        select 
            Course_Number as CourseNum, 
            Admin_Day as Day, 
            CONVERT(VARCHAR(10), Admin_Date, 101) as AdminDate, 
            Pre_MH_Instructions as PreTherapyInstr,
            Regimen_Instruction as TherapyInstr, 
            Post_MH_Instructions as PostTherapyInstr, 
            Template_ID as TemplateID
            from Master_Template 
            where Course_Number != 0 and
            Admin_Date >='" . $today . "' and 
            Admin_Date < '" . $EndDateSearch . "'" . "and 
            Regimen_ID = '" . $regimenId[0]['RegimenID'] . "' " . "and 
            Patient_ID = '" . $patientId . "' " . "
            order by Admin_Date
        ";
        

// error_log("Patient.Model.getTopLevelOEMRecordsNextThreeDays - $query");
        return $this->query($query);
    }

    // Gets the currently active template applied to the patient specified by $id
    function getTemplateIdByPatientID ($id)
    {
        // $query = "select Template_ID as id from Patient_Assigned_Templates where Patient_ID = '$id' and Is_Active = 1";
        $query = "select Template_ID as id from Patient_Assigned_Templates where Patient_ID = '$id' and Date_Ended_Actual is null";
        return $this->query($query);
    }

    function getTopLevelPatientTemplateDataById ($patientId, $id)
    {
            $query = "select 
            mt.Template_ID as id, 
            lu.Description as name, 
            mt.Cycle_Length as length, 
            mt.Emotegenic_ID as emoID, 
            l2.Name as emoLevel, 
            mt.Febrile_Neutropenia_Risk as fnRisk, 
            mt.Pre_MH_Instructions preMHInstruct, 
            mt.Post_MH_Instructions postMHInstruct, 
            mt.Cycle_Time_Frame_ID as CycleLengthUnitID, 
            l1.Name as CycleLengthUnit, 
            mt.Cancer_ID as Disease, 
            mt.Disease_Stage_ID as DiseaseStage, 
            mt.Regimen_ID RegimenId, 
            mt.Version as version, 
            case when l3.Name is not null then l3.Name else '' end as DiseaseStageName, 
            mt.Course_Number as CourseNum, 
            mt.Total_Courses as CourseNumMax, 
            mt.Regimen_Instruction as regimenInstruction, 
            Goal, 
            case when pt.Clinical_Trial is not null then pt.Clinical_Trial else '' end as ClinicalTrial, 
            Status, 
            l4.Name + '-' + l4.Description as PerfStatus 
            from Master_Template mt 
            INNER JOIN LookUp lu ON lu.Lookup_ID = mt.Regimen_ID 
            INNER JOIN LookUp l1 ON l1.Lookup_ID = mt.Cycle_Time_Frame_ID 
            INNER JOIN LookUp l2 ON l2.Lookup_ID = mt.Emotegenic_ID 
            INNER JOIN Patient_Assigned_Templates pt ON pt.Template_ID = mt.Template_ID 
            LEFT JOIN LookUp l4 ON l4.Lookup_ID = pt.Perf_Status_ID 
            LEFT OUTER JOIN LookUp l3 ON l3.Lookup_ID = mt.Disease_Stage_ID 
            where mt.Template_ID = '$id' and pt.Patient_ID = '$patientId' and pt.Is_Active = 1";
        return $this->query($query);
    }

    function getPatientIdByDFN ($dfn)
    {
        $query = "SELECT Patient_ID as id from Patient where DFN = '" . $dfn . "'";
        return $this->query($query);
    }

    function getPatientDFNByGUID($PatientID) {
        $query = "SELECT DFN from Patient where Patient_ID = '" . $PatientID . "'";
        return $this->query($query);
    }


    function saveAllergy ($form_data, $patientId)
    {
        if (empty($patientId)) {
            $patientId = $form_data->{'id'};
        }
        
        // MWB - 5/4/2012 Added checks for parameters below via "isset()".
        // Testing of new patients from MDWS indicated that data is NOT always
        // returned (e.d. AllergenName === "Not Assessed" doesn't return an ID
        // or Type)
        // Not sure if there are other cases where data isn't returned
        // completely, so checked all parameters
        if (isset($form_data->{'allergenId'}, $form_data->{'allergenName'}, 
                $form_data->{'allergenType'})) {
            $allergen = $form_data->{'allergenName'};
            $type = $form_data->{'allergenType'};
            $vaId = $form_data->{'allergenId'};
            if (isset($form_data->{'comment'})) {
                $comment = $form_data->{'comment'};
            } else {
                $comment = '';
            }
        } else {
            $retVal = array();
            $a = array();
            if (DB_TYPE == 'sqlsrv') {
                $x = array();
                $x['SQLSTATE'] = "None";
                $x['code'] = "None";
                $x['message'] = "Allergies Not Assessed for this patient";
                $a[0] = $x;
                $retVal['error'] = $a;
            } else if (DB_TYPE == 'mysql') {
                $retVal['error'] = "Allergies Not Assessed for this patient";
            }
            return $retVal;
        }
        
        /*
         * This is the allergen id used in MDWS. We may want to store this at
         * some point. $allergenId = $form_data->{'allergenId'};
         */
        
        $lookup = new LookUp();
        
        $lookupId = $lookup->getLookupIdByNameAndType($allergen, 29);
        
        if (! empty($lookupId[0])) {
            $lookupId = $lookupId[0]['id'];
        } else {
            $lookupId = $lookup->save(29, $allergen, $type);
            $lookupId = $lookupId[0]['lookupid'];
        }
        
        $query = "SELECT count(*) as count FROM Patient_Allergies where COMS_Allergen_ID = '" .
                 $lookupId . "' AND Patient_ID = '" . $patientId . "'";
        
        $allergyCount = $this->query($query);
        
        if ($allergyCount[0]['count'] > 0) {
            $allergyCount['apperror'] = "This allergen already exists for this patient.";
            return $allergyCount;
        }
        
        $query = "INSERT INTO Patient_Allergies(Patient_ID,VA_Allergen_ID,COMS_Allergen_ID,Comment) values(" .
                 "'" . $patientId . "'," . $vaId . ",'" . $lookupId . "','" .
                 $comment . "')";
        
        $savedRecord = $this->query($query);
        
        return $savedRecord;
    }

    function getAllergies ($patientId)
    {
        $allergies = "SELECT VA_Allergen_ID as id,l.Name as name,l.Description as type,Comment as comment " .
                 "FROM Patient_Allergies pa " .
                 "INNER JOIN LookUp l ON l.Lookup_ID = pa.COMS_Allergen_ID " .
                 "WHERE pa.Patient_ID = '" . $patientId . "'";
        
        return $this->query($allergies);
    }

    function saveLabInfo ($labInfo, $patientId)
    {
        if (empty($patientId)) {
            $patientId = $labInfo->id;
        }
        
        $releaseDate = new DateTime($labInfo->releaseDate);
        $author = $labInfo->author;
        $specimen = $labInfo->specimen;
        $specInfo = $labInfo->specInfo;
        $specColDate = new DateTime($labInfo->specColDate);
        $results = $labInfo->Results;
        $comment = $labInfo->comment;
        
        $query = "SELECT CONVERT(VARCHAR,Date_Created,121) as Date_Created FROM Lab_Info where Patient_ID = '" .
                 $patientId . "' " . "AND Release_Date = '" .
                 $releaseDate->format('Y-m-d') . "' " . "AND Author = '" .
                 $author . "' AND Specimen = '" . $specimen .
                 "' AND Specimen_Info = '" . $specInfo . "' " .
                 "AND Spec_Col_Date ='" . $specColDate->format('Y-m-d') .
                 "' AND Comment = '" . $comment . "'";
        
        $exist = $this->query($query);
        
        $labInfoId = null;
        
        if (! empty($exist) && count($exist) > 0) {
            $query = "SELECT ID FROM Lab_Info where Patient_ID = '" . $patientId .
                     "' AND Date_Created = '" . $exist[0]['Date_Created'] . "'";
            
            $retVal = $this->query($query);
            
            $labInfoId = $retVal[0]['ID'];
        } else {
            $currDate = $this->getCurrentDate();
            
            $query = "INSERT INTO Lab_Info (Patient_ID,Release_Date,Author,Specimen,Specimen_Info,Spec_Col_Date,Comment,Date_Created) values(" .
                     "'" . $patientId . "','" . $releaseDate->format('Y-m-d') .
                     "','" . $author . "','" . $specimen . "','" . $specInfo .
                     "','" . $specColDate->format('Y-m-d') . "'," . "'" .
                     $comment . "','" . $currDate . "')";
            
            $retVal = $this->query($query);
            
            if (null != $retVal && array_key_exists('error', $retVal)) {
                return $retVal;
            }
            
            $query = "SELECT ID FROM Lab_Info where Patient_ID = '" . $patientId .
                     "' AND Date_Created = '" . $currDate . "'";
            
            $retVal = $this->query($query);
            
            $labInfoId = $retVal[0]['ID'];
        }
        
        foreach ($results as $result) {
            
            $mdwsid = $result->mdwsid;
            $name = $result->name;
            $units = htmlentities($result->units);
            $testResult = $result->result;
            $refRange = explode('-', $result->refRange);
            $siteId = $result->siteId;
            
            $currDate = $this->getCurrentDate();
            
            if (isset($result->boundaryStatus) && ! empty(
                    $result->boundaryStatus)) {
                $boundaryStatus = true;
            } else {
                $boundaryStatus = false;
            }
            
            $query = "SELECT count(*) as count FROM Lab_Info_Results " .
                     "WHERE Lab_Info_ID = '" . $labInfoId .
                     "' AND Lab_Test_Name = '" . $name .
                     "' AND Lab_Test_Units = '" . $units . "' " .
                     "AND Lab_Test_Result = '" . $testResult .
                     "' AND MDWS_Lab_Result_ID = " . $mdwsid .
                     " AND Accept_Range_Low = '" . $refRange[0] . "' " .
                     "AND Accept_Range_High = '" . $refRange[1] .
                     "' AND Site_ID = " . $siteId . " AND Out_Of_Range = '" .
                     $boundaryStatus . "'";
            
            $labInfoCount = $this->query($query);
            
            if ($labInfoCount[0]['count'] > 0) {
                $labInfoCount['apperror'] = "This Lab Info Result already exists for this patient.";
                return $labInfoCount;
            }
            
            $query = "INSERT INTO Lab_Info_Results (Lab_Info_ID,Lab_Test_Name,Lab_Test_Units,Lab_Test_Result,MDWS_Lab_Result_ID,Accept_Range_Low," .
                     "Accept_Range_High,Site_ID,Out_Of_Range,Date_Created) values(" .
                     "'" . $labInfoId . "','" . $name . "','" . $units . "','" .
                     $testResult . "'," . $mdwsid . ",'" . $refRange[0] . "','" .
                     $refRange[1] . "'," . $siteId . "," . "'" . $boundaryStatus .
                     "','" . $currDate . "')";
            
            $retVal = $this->query($query);
            
            if (null != $retVal && array_key_exists('error', $retVal)) {
                return $retVal;
            }
        }
    }

    function getLabInfoForPatient ($patientId)
    {
        $query = "SELECT ID,CONVERT(VARCHAR(10),Release_Date, 101) as relDate,Author as author,Specimen as specimen,Specimen_Info as specInfo," .
                 "CONVERT(VARCHAR(10),Spec_Col_Date, 101) as specColDate,Comment as comment " .
                 "FROM Lab_Info where Patient_ID = '" . $patientId . "'";
        
        return $this->query($query);
    }

    function getLabInfoResults ($labInfoId)
    {
        $query = "SELECT ID,Lab_Test_Name as name,Lab_Test_Units as units,Lab_Test_Result as result,MDWS_Lab_Result_ID as mdwsId, " .
                 "Accept_Range_Low + ' - ' + Accept_Range_High as acceptRange,Site_ID as site,Out_Of_Range as outOfRange " .
                 "FROM Lab_Info_Results where Lab_Info_ID = '" . $labInfoId . "'";
        
        return $this->query($query);
    }

    /**
     *
     * @param string $orderId            
     * @param string $orderStatus            
     * @return array
     */
    public function updateOrderStatus ($orderId, $orderStatus)
    {
        $query = "
    	    UPDATE Order_Status SET 
    	        Order_Status = '$orderStatus' 
    	    WHERE Order_ID = '$orderId'
    	";
// error_log("Patient.Model.updateOrderStatus - $query");
        return $this->query($query);
    }

    /**
     *
     * @param string $patientId            
     * @param string $drug            
     * @param string $orderStatus            
     */
    public function updateOrderStatusByPatientIdAndDrugName ($patientId, $drugName, $orderStatus, $Order_ID) {
        $query = "
	        UPDATE Order_Status SET
	            Order_Status = '$orderStatus'
	        WHERE Patient_ID = '$patientId'
	            AND Drug_Name = '$drugName'
                AND Order_ID = '$Order_ID'
	    ";
// error_log("Patient.Model.updateOrderStatusByPatientIdAndDrugName - $query");
        return $this->query($query);
    }

    /**
     *
     * @param string $orderId            
     * @return array
     */
    public function getPatientIdByOrderId ($orderId)
    {
        $query = "
	        SELECT Patient_ID
	        FROM Order_Status
	        WHERE Order_ID = '$orderId'
	    ";
        
        return $this->query($query);
    }

    function updateOrderStatusTable ($TID, $Drug_Name, $Order_Type, $PID, 
            $Order_ID, $Order_Status)
    {
        if ($Order_ID === '') {
            $Order_Status = "First Entry";
            $query = "INSERT INTO Order_Status(Template_ID, Order_Status, Drug_Name, Patient_ID) VALUES ('" .
                     $TID . "','" . $Order_Status . "','" . $Drug_Name . "','" .
                     $PID . "'')";
        } else {
            $Order_Status = "Else";
            $query = "Update Order_Status set Order_Status = '" . $Order_Status .
                     "',Drug_Name = '" . $Drug_Name . "' " .
                     "where Template_ID = '" . $Template_ID .
                     "' AND Drug_Name = '" . $Drug_Name . "' AND Patient_ID = '" .
                     $PID . "'";
        }
        
        $this->query($query);
    }

    function updateOrderStatusIn ($TID, $Drug_Name, $Order_Type, $PID, $Notes)
    {
        $Template_IDchk = NULL;
        $Drug_Namechk = NULL;
        
        $query = "SELECT Template_ID as Template_ID_CHK, Drug_Name as Drug_Name_CHK, Order_Type as Order_Type " .
                 "FROM Order_Status " . "WHERE Template_ID = '" . $TID . "' " .
                 "AND Drug_Name = '" . $Drug_Name . "'";
        $queryq = $this->query($query);
        foreach ($queryq as $row) {
            $Template_IDchk = $row['Template_ID_CHK'];
            $Drug_Namechk = $row['Drug_Name_CHK'];
        }
        if ($Template_IDchk === NULL) {
            $query = "INSERT INTO Order_Status(Template_ID, Order_Status, Drug_Name, Order_Type, Patient_ID, Notes) VALUES ('$TID','Finalized','$Drug_Name','$Order_Type','$PID','Line 1325')";
        } else {
            $query = "Update Order_Status set Order_Status = 'Finalized' " .
                     "where Template_ID = '" . $TID . "' " . "AND Drug_Name = '" .
                     $Drug_Name . "' " . "AND Patient_ID = '" . $PID . "'";
        }
        
        $this->query($query);
    }

    function LookupNameIn ($LID)
    {
        $query = "SELECT Name as LK_Name FROM LookUp WHERE Lookup_ID = '" . $LID .
                 "'";
        $queryq = $this->query($query);
        foreach ($queryq as $row) {
            $LK_Name = $row['LK_Name'];
        }
        return $LK_Name;
    }

    function LookupPatientID ($oemrecordid)
    {
        $query = "SELECT Patient_ID as LK_Patient_ID FROM Master_Template WHERE Template_ID = '" .
                 $oemrecordid . "'";
        $queryq = $this->query($query);
        foreach ($queryq as $row) {
            $patientid = $row['LK_Patient_ID'];
        }
        return $patientid;
    }

    function OEMupdateOrderStatus ($form_data)
    {
        $TID = $form_data->{'TemplateID'};
        // $patientid = $form_data->{'PatientId'};
        // $patientid = $this->LookupPatientID($OEMRecordID);
        // foreach ($preHydrations as $preorderRecord) {
        
        // prehydration
        // $Drug_Name = $preorderRecord['drug'];
        // $DrugID = $preorderRecord['id'];
        // $Order_Type = $preorderRecord['type'];
        
        $Order_Status = "In-Coordination";
        
        $query = "Update Order_Status set Order_Status = '" . $OrderStatusF .
                 "',Drug_Name = '" . $Drug_NameF . "' " . "where Order_ID = '" .
                 $OrderIDF . "' ";
        
        $this->query($query);
        // }
    }

    function LookupName ($LID)
    {
        $query = "SELECT Name as LK_Name FROM LookUp WHERE Lookup_ID = '" . $LID .
                 "'";
        $queryq = $this->query($query);
        foreach ($queryq as $row) {
            $LK_Name = $row['LK_Name'];
        }
        return $LK_Name;
    }

    function LookupDescription ($LID)
    {
        $query = "SELECT Description as LK_Description FROM LookUp WHERE Lookup_ID = '" .
                 $LID . "'";
        $queryq = $this->query($query);
        foreach ($queryq as $row) {
            $LK_Description = $row['LK_Description'];
        }
        return $LK_Description;
    }

    function selectCDH ($PatientID,$CDHID)
    {
        $query = "
		SELECT ID,
		Patient_ID,
		MedID,
		CumulativeDoseAmt,
		CumulativeDoseUnits,
		Date_Changed,
		Author
		FROM Patient_CumulativeDoseHistory
		WHERE Patient_ID = '$PatientID'"; 
		$result = $this->query($query);
        return $result;
    }

    function selectCDHR ($PatientID,$CDHID)
    {
        $query = "
		SELECT ID,
		Patient_ID,
		MedID,
		CumulativeDoseAmt,
		CumulativeDoseUnits,
		Date_Changed,
		Author
		FROM Patient_CumulativeDoseHistory
		WHERE ID = '$CDHID'"; 
		$result = $this->query($query);
        return $result;
    }

    function insertCDHR ($PatientID,$MedID,$CumulativeDoseAmt,$CumulativeDoseUnits)
    {
		$newidquery = "SELECT NEWID()";
		$GUID = $this->query($newidquery);
		$GUID = $GUID[0][""];
        
		$query = "INSERT INTO Patient_CumulativeDoseHistory
           (ID,
           Patient_ID
           ,MedID
           ,CumulativeDoseAmt
           ,CumulativeDoseUnits
		   ,Author)
     VALUES
           ('$GUID',
           '$PatientID'
           ,'$MedID'
           ,'$CumulativeDoseAmt'
           ,'$CumulativeDoseUnits'
		   ,'".$_SESSION['rid']."')";
		   
		$result = $this->query($query);
        
		return $GUID;
    }

    function updateCDHR ($CDHID,$CumulativeDoseAmt,$CumulativeDoseUnits)
    {
		$newidquery = "SELECT NEWID()";
		$GUID = $this->query($newidquery);
		$GUID = $GUID[0][""];
        $query = "UPDATE Patient_CumulativeDoseHistory
        SET CumulativeDoseAmt = '$CumulativeDoseAmt',
            CumulativeDoseUnits = '$CumulativeDoseUnits',
            Author = '".$_SESSION['rid']."'
        WHERE ID = '$CDHID'";

		$result = $this->query($query);
		return 'updated';
    }

    function UpdateAdminDateMT ($Template_ID,$Admin_Date) {
        $query = "UPDATE Master_Template SET Admin_Date = '$Admin_Date' WHERE Template_ID = '$Template_ID'";
        $result = $this->query($query);
    }

    function UpdateOrderStatusAdminDate($Therapy, $AdminDate) {
        foreach($Therapy as $aRec) {
//          error_log("UpdateOrderStatusAdminDate = ");
//          error_log(json_encode($aRec));
            $Order_ID = $aRec->Order_ID;
            $query = "UPDATE Order_Status SET Admin_Date = '$AdminDate' WHERE Order_ID = '$Order_ID'";
//          error_log("UpdateOrderStatusAdminDate = $query");
            $result = $this->query($query);
        }
    }


    function doMedRemindersData($fcn, $TemplateID, $MR_ID, $InputData) {
        $InsertBuf1 = array();
        $InsertBuf2 = array();
        $UpdateBuf = array();

        if (isset($InputData->Title)) {
            $Title = $this->escapeString($InputData->Title);
            $InsertBuf1[] = "Title";
            $InsertBuf2[] = "'$Title'";
            $UpdateBuf[] = "Title = '$Title'";
        }
        if (isset($InputData->Description)) {
            $Description = $this->escapeString($InputData->Description);
            $InsertBuf1[] = "Description";
            $InsertBuf2[] = "'$Description'";
            $UpdateBuf[] = "Description = '$Description'";
        }
        if (isset($InputData->ReminderWhenCycle)) {
            $ReminderWhenCycle = $InputData->ReminderWhenCycle;
            $InsertBuf1[] = "ReminderWhenCycle";
            $InsertBuf2[] = "'$ReminderWhenCycle'";
            $UpdateBuf[] = "ReminderWhenCycle = '$ReminderWhenCycle'";
        }
        if (isset($InputData->ReminderWhenPeriod)) {
            $ReminderWhenPeriod = $InputData->ReminderWhenPeriod;
            $InsertBuf1[] = "ReminderWhenPeriod";
            $InsertBuf2[] = "'$ReminderWhenPeriod'";
            $UpdateBuf[] = "ReminderWhenPeriod = '$ReminderWhenPeriod'";
        }

        if ("update" == $fcn) {
            $query = "UPDATE Med_Reminders SET " . implode(", ", $UpdateBuf) . " where MR_IR = '$MR_ID'";
        }
        else {
            $InsertBuf1[] = "MR_ID";
            $InsertBuf2[] = "'" . $MR_ID . "'";
            $InsertBuf1[] = "TemplateID";
            $InsertBuf2[] = "'$TemplateID'";
            $query = "INSERT into Med_Reminders (" . implode(", ", $InsertBuf1) . ") VALUES (" . implode(", ", $InsertBuf2) . ")";
        }
        return $this->query($query);
    }

    function setMedReminders($TemplateID, $MR_ID, $_POST) {
        return $this->doMedRemindersData("insert", $TemplateID, $MR_ID, $_POST);
    }

    function updateMedReminders($TemplateID, $MR_ID, $_POST) {
        return $this->doMedRemindersData("update", $TemplateID, $MR_ID, $_POST);
    }

    function getMedReminders($TemplateID, $MR_ID) {
        $buf = "";
        if ($TemplateID) {
            $buf = "TemplateID = '$TemplateID'";
        }
        if ($MR_ID) {
            if ($buf) {
                $buf .= " and ";
            }
            $buf .= " MR_ID = '$MR_ID'";
        }
        if ($buf) {
            $buf = " where " . $buf;
        }
        $query = "SELECT * FROM Med_Reminders" . $buf;
        // error_log($query);
        return $this->query($query);
    }

    function getApproverOfRegimen($Patient_ID) {
        $Today = date('m/d/Y');

        $query = "SELECT AssignedByUser, ApprovedByUser FROM Patient_Assigned_Templates where Patient_ID = '$Patient_ID' and Date_Started <= '$Today' and Date_Ended_Actual is NULL";
        $retVal = $this->query($query);
// error_log("Patient Model - getApproverOfRegimen - $query");
// error_log("Patient Model - getApproverOfRegimen - " . json_encode($retVal));

        if (null === $retVal) {
// error_log("Patient Model - getApproverOfRegimen - ERROR - No Return Results");
            return "";
        }
        if (array_key_exists('error', $retVal)) {
// error_log("Patient Model - getApproverOfRegimen - ERROR - " . json_encode($retVal));
            return "";
        }
        $ApprovedByUser = '';
        $AssignedByUser = '';

        $ApprovedByUser = $retVal[0]['ApprovedByUser'];
        return $ApprovedByUser;
    }

}
