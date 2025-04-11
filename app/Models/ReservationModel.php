<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\MainOperation;

class ReservationModel extends Model
{    
    protected $DBGroup          = 'default';
    protected $table            = 'ci_sessions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['ip_address', 'timestamp', 'data'];

    public function getDataReservation($idPartnerType, $idPartner, $confirmation, $page, $orderByStr, $orderType, $reservationStatus, $startActivityDate, $endActivityDate, $bookingCode, $customerName, $locationName, $transportService, $searchKeyword)
    {	
        $mainOperation          =   new MainOperation();
        $dataPerPage            =   25;
        $tableName              =   $idPartnerType == 1 ? "t_schedulevendor" : "t_scheduledriver";
        $fieldIdSchedule        =   $idPartnerType == 1 ? "IDSCHEDULEVENDOR" : "IDSCHEDULEDRIVER";
        $fieldWhere             =   $idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";
        $fieldTimeSchedule      =   $idPartnerType == 1 ? "A.TIMESCHEDULE" : "D.RESERVATIONTIMESTART";
        $fieldAdult             =   $idPartnerType == 1 ? "C.PAXADULT" : "D.NUMBEROFADULT";
        $fieldChild             =   $idPartnerType == 1 ? "C.PAXCHILD" : "D.NUMBEROFCHILD";
        $fieldInfant            =   $idPartnerType == 1 ? "C.PAXINFANT" : "D.NUMBEROFINFANT";
        $fieldTimeBook          =   $idPartnerType == 1 ? "LEFT(A.TIMEBOOKING, 5)" : "LEFT(D.RESERVATIONTIMESTART, 5)";
        $con_confirmation       =   isset($confirmation) && $confirmation ? "A.STATUSCONFIRM = 1" : "A.STATUSCONFIRM = 0";
        $con_reservationStatus  =   isset($reservationStatus) && $reservationStatus != "" ? "A.STATUS = ".$reservationStatus : "1=1";
        $con_scheduleDate       =   isset($startActivityDate) && $startActivityDate != "" && isset($endActivityDate) && $endActivityDate != "" ?
                                        "DATE(B.SCHEDULEDATE) BETWEEN '".$startActivityDate."' AND '".$endActivityDate."'" :
                                        "1=1";
        $con_likeBookingCode    =   isset($bookingCode) && $bookingCode != "" ? "D.BOOKINGCODE LIKE '%".$bookingCode."%'" : "1=1";
        $con_likeCustomerName   =   isset($customerName) && $customerName != "" ? "D.CUSTOMERNAME LIKE '%".$customerName."%'" : "1=1";
        $con_likeLocation       =   isset($locationName) && $locationName != "" && $transportService ? "(D.HOTELNAME LIKE '%".$locationName."%' OR D.PICKUPLOCATION LIKE '%".$locationName."%')" : "1=1";
        $con_likeSearchKeyWord  =   isset($searchKeyword) && $searchKeyword != "" && $searchKeyword ? "(D.CUSTOMERNAME LIKE '%".$searchKeyword."%' OR D.BOOKINGCODE LIKE '%".$searchKeyword."%' OR B.PRODUCTNAME LIKE '%".$searchKeyword."%')" : "1=1";
        $baseQuery              =   "SELECT A.".$fieldIdSchedule.", DATE_FORMAT(B.SCHEDULEDATE, '%d %b %Y') AS DATEACTIVITY, ".$fieldTimeBook." AS TIMEBOOKING, LEFT(D.RESERVATIONTIMESTART, 5) AS PICKUPTIME,
                                            IFNULL(LEFT(".$fieldTimeSchedule.", 5), '00:00') AS TIMESCHEDULE, DATE_FORMAT(B.DATETIMEINPUT, '%d %b %Y %H:%i') AS DATETIMERECEPTION, IFNULL(DATE_FORMAT(A.DATETIMECONFIRM, '%d %b %Y %H:%i'), '-') AS DATETIMECONFIRM,
                                            IF(A.USERCONFIRM IS NULL OR A.USERCONFIRM = '', '-', A.USERCONFIRM) AS USERCONFIRM, E.SOURCENAME, D.BOOKINGCODE, D.RESERVATIONTITLE, B.PRODUCTNAME,
                                            D.CUSTOMERNAME, D.CUSTOMERCONTACT, D.CUSTOMEREMAIL, IFNULL(".$fieldAdult.", 0) AS PAXADULT, IFNULL(".$fieldChild.", 0) AS PAXCHILD, IFNULL(".$fieldInfant.", 0) AS PAXINFANT,
                                            D.REMARK, D.HOTELNAME, D.PICKUPLOCATION, IF(COUNT(F.IDCOLLECTPAYMENT) > 0, 1, 0) AS STATUSINCLUDECOLLECT, IF(D.IDAREA = -1, 'Without Transfer',
                                            IFNULL(G.AREANAME, '-')) AS AREANAME, IF(D.IDAREA = -1, '-', IFNULL(G.AREATAGS, '-')) AS AREATAGS, IFNULL(C.PRICEPERPAXADULT, 0) AS PRICEPERPAXADULT,
                                            IFNULL(C.PRICEPERPAXCHILD, 0) AS PRICEPERPAXCHILD, IFNULL(C.PRICETOTALADULT, 0) AS PRICETOTALADULT, IFNULL(C.PRICETOTALCHILD, 0) AS PRICETOTALCHILD
                                    FROM ".$tableName." A
                                    LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
                                    LEFT JOIN t_reservationdetailsticket C ON A.IDRESERVATIONDETAILS = C.IDRESERVATIONDETAILS
                                    LEFT JOIN t_reservation D ON B.IDRESERVATION = D.IDRESERVATION
                                    LEFT JOIN m_source E ON D.IDSOURCE = E.IDSOURCE
                                    LEFT JOIN t_collectpayment F ON D.IDRESERVATION = F.IDRESERVATION
								    LEFT JOIN m_area G ON D.IDAREA = G.IDAREA
                                    WHERE A.".$fieldWhere." = ".$idPartner." AND ".$con_confirmation." AND ".$con_reservationStatus." AND ".$con_scheduleDate." AND ".$con_likeBookingCode." AND
                                          ".$con_likeCustomerName." AND ".$con_likeLocation." AND ".$con_likeSearchKeyWord." AND A.".$fieldIdSchedule." != '' AND A.".$fieldIdSchedule." IS NOT NULL
                                    GROUP BY A.".$fieldIdSchedule."
                                    HAVING COUNT(A.".$fieldIdSchedule.") > 0
                                    ORDER BY ".$orderByStr." ".$orderType;
        $result                 =   $mainOperation->execQueryWithLimit($baseQuery, $page, $dataPerPage);

        if(is_null($result)) return $mainOperation->generateEmptyResult();
        return $mainOperation->generateResultPagination($result, $baseQuery, $fieldIdSchedule, $page, $dataPerPage);
	}

    public function getDetailDataReservation($idPartnerType, $idSchedule)
    {	
        $tableName      =   $idPartnerType == 1 ? "t_schedulevendor" : "t_scheduledriver";
        $fieldWhere     =   $idPartnerType == 1 ? "A.IDSCHEDULEVENDOR" : "A.IDSCHEDULEDRIVER";
        $fieldAdult     =   $idPartnerType == 1 ? "C.PAXADULT" : "D.NUMBEROFADULT";
        $fieldChild     =   $idPartnerType == 1 ? "C.PAXCHILD" : "D.NUMBEROFCHILD";
        $fieldInfant    =   $idPartnerType == 1 ? "C.PAXINFANT" : "D.NUMBEROFINFANT";
        $fieldTimeBook  =   $idPartnerType == 1 ? "LEFT(A.TIMEBOOKING, 5)" : "LEFT(D.RESERVATIONTIMESTART, 5)";
        $this->select("DATE_FORMAT(B.SCHEDULEDATE, '%d %b %Y') AS DATEACTIVITY, LEFT(D.RESERVATIONTIMESTART, 5) AS PICKUPTIME,
                    D.RESERVATIONTITLE, B.PRODUCTNAME, D.CUSTOMERNAME, IFNULL(".$fieldAdult.", 0) AS PAXADULT, IFNULL(".$fieldChild.", 0) AS PAXCHILD,
                    IFNULL(".$fieldInfant.", 0) AS PAXINFANT, D.REMARK, A.IDRESERVATIONDETAILS, ".$fieldTimeBook." AS TIMEBOOKING");
        $this->from($tableName.' AS A', true);
        $this->join('t_reservationdetails AS B', 'A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS', 'LEFT');
        $this->join('t_reservationdetailsticket AS C', 'A.IDRESERVATIONDETAILS = C.IDRESERVATIONDETAILS', 'LEFT');
        $this->join('t_reservation AS D', 'B.IDRESERVATION = D.IDRESERVATION', 'LEFT');
        $this->where($fieldWhere, $idSchedule);
        $this->where('A.STATUSCONFIRM', 0);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return false;
        return $row;
	}
}
