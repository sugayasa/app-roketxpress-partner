<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\MainOperation;

class CollectPaymentModel extends Model
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

    public function getDataCollectPayment($page = 1, $dataPerPage = 25, $orderBy, $orderType, $idPartnerType, $idPartner, $startDate, $endDate, $collectStatus, $settlementStatus, $searchKeyword, $viewActiveOnly)
    {	
        $mainOperation          =   new MainOperation();
        $fieldWhere             =   $idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";
        $con_collectDate        =   isset($startDate) && $startDate != "" && isset($endDate) && $endDate != "" && !$viewActiveOnly   ?
                                        "DATE(A.DATECOLLECT) BETWEEN '".$startDate."' AND '".$endDate."'" :
                                        "1=1";
        $con_collectStatus      =   isset($collectStatus) && $collectStatus && !$viewActiveOnly ? "A.STATUS = ".$collectStatus : "1=1";
        $con_settlementStatus   =   isset($settlementStatus) && $settlementStatus != "" && !$viewActiveOnly ? "A.STATUSSETTLEMENTREQUEST = ".$settlementStatus : "1=1";
        $con_likeSearchKeyword  =   isset($searchKeyword) && $searchKeyword != "" && !$viewActiveOnly ? "(B.CUSTOMERNAME LIKE '%".$searchKeyword."%' OR B.BOOKINGCODE LIKE '%".$searchKeyword."%' OR B.REMARK LIKE '%".$searchKeyword."%' OR D.DESCRIPTION LIKE '%".$searchKeyword."%')" : "1=1";
        $con_viewActiveOnly     =   $viewActiveOnly ? "A.STATUS = 0" : "1=1";
        $baseQuery              =   "SELECT A.IDCOLLECTPAYMENT, DATE_FORMAT(A.DATECOLLECT, '%d %b %Y') AS DATECOLLECT, C.SOURCENAME, B.DURATIONOFDAY, B.RESERVATIONTITLE,
                                            DATE_FORMAT(B.RESERVATIONDATESTART, '%d %b %Y') AS RESERVATIONDATESTART, DATE_FORMAT(B.RESERVATIONDATEEND, '%d %b %Y') AS RESERVATIONDATEEND,
                                            B.CUSTOMERNAME, B.BOOKINGCODE, B.REMARK, D.DESCRIPTION, D.AMOUNTCURRENCY, D.AMOUNT, D.EXCHANGECURRENCY, D.AMOUNTIDR, A.STATUS, A.STATUSSETTLEMENTREQUEST
                                    FROM t_collectpayment A
                                    LEFT JOIN t_reservation B ON A.IDRESERVATION = B.IDRESERVATION
                                    LEFT JOIN m_source C ON B.IDSOURCE = C.IDSOURCE
                                    LEFT JOIN t_reservationpayment D ON A.IDRESERVATIONPAYMENT = D.IDRESERVATIONPAYMENT
                                    WHERE A.IDPARTNERTYPE = ".$idPartnerType." AND A.".$fieldWhere." = ".$idPartner." AND ".$con_collectDate." AND ".$con_collectStatus." AND ".$con_settlementStatus." AND ".$con_likeSearchKeyword." AND ".$con_viewActiveOnly."
                                    ORDER BY ".$orderBy." ".$orderType;
        $result                 =   $mainOperation->execQueryWithLimit($baseQuery, $page, $dataPerPage);

        if(is_null($result)) return $mainOperation->generateEmptyResult();
        return $mainOperation->generateResultPagination($result, $baseQuery, 'IDCOLLECTPAYMENT', $page, $dataPerPage);
	}

    public function getListReservationCollectPayment($idPartnerType, $idPartner, $reservationDateStartStr, $reservationDateEndStr, $reservationKeyword)
    {	
        $tableSchedule  =   $idPartnerType == 1 ? "t_schedulevendor" : "t_scheduledriver";
        $fieldWhere     =   $idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";
        $this->select("B.IDRESERVATION, C.DURATIONOFDAY, DATE_FORMAT(C.RESERVATIONDATESTART, '%d %b %Y') AS RESERVATIONDATESTART,
                DATE_FORMAT(C.RESERVATIONDATEEND, '%d %b %Y') AS RESERVATIONDATEEND, DATE_FORMAT(B.SCHEDULEDATE, '%d %b %Y') AS ACTIVITYDATE,
                D.SOURCENAME, C.BOOKINGCODE, C.RESERVATIONTITLE, C.CUSTOMERNAME, C.CUSTOMERCONTACT, C.CUSTOMEREMAIL,
                CONCAT(C.NUMBEROFADULT, ' Adult | ', C.NUMBEROFCHILD, ' Child | ', C.NUMBEROFINFANT, ' Infant') AS PAXDETAIL,
                B.SCHEDULEDATE");
        $this->from($tableSchedule.' AS A', true);
        $this->join('t_reservationdetails AS B', 'A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS', 'LEFT');
        $this->join('t_reservation AS C', 'B.IDRESERVATION = C.IDRESERVATION', 'LEFT');
        $this->join('m_source AS D', 'C.IDSOURCE = D.IDSOURCE', 'LEFT');
        $this->join('t_fee AS E', 'A.IDRESERVATIONDETAILS = E.IDRESERVATIONDETAILS', 'LEFT');
        $this->where('A.'.$fieldWhere, $idPartner);
        $this->where('DATE(B.SCHEDULEDATE) >=', $reservationDateStartStr);
        $this->where('DATE(B.SCHEDULEDATE) <=', $reservationDateEndStr);

        $this->groupStart();
        $this->where('E.IDFEE', null)->orWhere('E.WITHDRAWSTATUS', 0);
        $this->groupEnd();

        if(isset($reservationKeyword) && $reservationKeyword != "") {
            $this->groupStart();
            $this->like('C.BOOKINGCODE', $reservationKeyword)
            ->orLike('C.RESERVATIONTITLE', $reservationKeyword)
            ->orLike('C.CUSTOMERNAME', $reservationKeyword);
            $this->groupEnd();
        }

        $this->orderBy("DATE_FORMAT(C.RESERVATIONDATESTART, '%Y-%M-%d'), C.BOOKINGCODE");

        $result =   $this->get()->getResultObject();
        
        if(is_null($result)) return false;
        return $result;
	}

    public function getDetailCollectPayment($idCollectPayment)
    {	
        $this->select("C.SOURCENAME, B.BOOKINGCODE, B.RESERVATIONTITLE, B.DURATIONOFDAY, DATE_FORMAT(B.RESERVATIONDATESTART, '%d %b %Y') AS RESERVATIONDATESTART,
                    DATE_FORMAT(B.RESERVATIONDATEEND, '%d %b %Y') AS RESERVATIONDATEEND, LEFT(B.RESERVATIONTIMESTART, 5) AS RESERVATIONTIMESTART,
                    LEFT(B.RESERVATIONTIMEEND, 5) AS RESERVATIONTIMEEND, B.CUSTOMERNAME, B.CUSTOMERCONTACT, B.CUSTOMEREMAIL, DATE_FORMAT(A.DATECOLLECT, '%d %b %Y') AS DATECOLLECT,
                    D.AMOUNTCURRENCY, D.AMOUNT, D.EXCHANGECURRENCY, D.AMOUNTIDR, B.REMARK, D.DESCRIPTION, A.STATUS, A.STATUSSETTLEMENTREQUEST, A.IDVENDOR,
                    A.DATECOLLECT AS DATECOLLECTDB, CONCAT('".URL_COLLECT_PAYMENT_RECEIPT."', 'noimage.jpg') AS SETTLEMENTRECEIPT, A.IDWITHDRAWALRECAP");
        $this->from('t_collectpayment AS A', true);
        $this->join('t_reservation AS B', 'A.IDRESERVATION = B.IDRESERVATION', 'LEFT');
        $this->join('m_source AS C', 'B.IDSOURCE = C.IDSOURCE', 'LEFT');
        $this->join('t_reservationpayment AS D', 'A.IDRESERVATIONPAYMENT = D.IDRESERVATIONPAYMENT', 'LEFT');
        $this->where('A.IDCOLLECTPAYMENT', $idCollectPayment);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return false;
        return $row;
	}

    public function getHistoryCollectPayment($idCollectPayment)
    {	
        $this->select("DATE_FORMAT(DATETIMEINPUT, '%d %b %Y %H:%i') AS DATETIMEINPUT, DESCRIPTION, USERINPUT, STATUS,
                    IF(SETTLEMENTRECEIPT = '', '', CONCAT('".URL_COLLECT_PAYMENT_RECEIPT."', SETTLEMENTRECEIPT)) AS SETTLEMENTRECEIPT,
                    IDCOLLECTPAYMENTHISTORY");
        $this->from('t_collectpaymenthistory', true);
        $this->where('IDCOLLECTPAYMENT', $idCollectPayment);
        $this->orderBy("DATE_FORMAT(DATETIMEINPUT, '%Y%m%d%H%i%s')");

        $row    =   $this->get()->getResultObject();

        if(is_null($row)) return false;
        return $row;
	}

    public function getTotalSettlementRequest()
    {	
        $this->select("COUNT(IDCOLLECTPAYMENT) AS TOTALSETTLEMENTREQUEST");
        $this->from('t_collectpayment', true);
        $this->where('STATUSSETTLEMENTREQUEST', 1);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return 0;
        return $row['TOTALSETTLEMENTREQUEST'];
	}

    public function getDetailCollectPaymentSchedule($idReservation, $idPartnerType, $idVendor, $idDriver, $dateSchedule)
    {	
        $this->select("A.IDCOLLECTPAYMENT, SUM(B.AMOUNTIDR) AS TOTALAMOUNTIDRCOLLECTPAYMENT, IFNULL(GROUP_CONCAT(B.DESCRIPTION), '-') AS DESCRIPTIONCOLLECTPAYMENT,
                    MIN(A.STATUS) AS STATUSCOLLECTPAYMENT");
        $this->from('t_collectpayment AS A', true);
        $this->join('t_reservationpayment AS B', 'A.IDRESERVATIONPAYMENT = B.IDRESERVATIONPAYMENT', 'LEFT');
        $this->where('A.IDRESERVATION', $idReservation);
        if($idPartnerType == 1) $this->where('A.IDVENDOR', $idVendor);
        if($idPartnerType == 2) $this->where('A.IDDRIVER', $idDriver);
        $this->groupStart();
        $this->where('A.DATECOLLECT', $dateSchedule);
        $this->orGroupStart();
        $this->where('A.DATECOLLECT <=', $dateSchedule);
        $this->where('A.STATUS', 0);
        $this->groupEnd();
        $this->groupEnd();
        $this->groupBy('A.IDRESERVATION');
        $this->orderBy('A.STATUS ASC');
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return false;
        return $row;
	}

    public function getStrArrIdCollectPaymentByDateReservation($idReservation, $idPartnerType, $idVendor, $idDriver, $collectDate)
    {	
        $this->select("GROUP_CONCAT(IDCOLLECTPAYMENT) AS STRARRIDCOLLECTPAYMENT");
        $this->from('t_collectpayment', true);
        $this->where('IDRESERVATION', $idReservation);
        if($idPartnerType == 1) $this->where('IDVENDOR', $idVendor);
        if($idPartnerType == 2) $this->where('IDDRIVER', $idDriver);
        $this->where('DATECOLLECT <= ', $collectDate);
        $this->where('STATUS', 0);
        $this->groupBy('IDRESERVATION');
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return false;
        return $row['STRARRIDCOLLECTPAYMENT'];
	}

    public function getDetailPayment($idPartnerType, $idVendor, $idDriver, $idCollectPayment)
    {	
        $this->select("A.IDRESERVATIONPAYMENT, B.DESCRIPTION");
        $this->from('t_collectpayment AS A', true);
        $this->join('t_reservationpayment AS B', 'A.IDRESERVATIONPAYMENT = B.IDRESERVATIONPAYMENT', 'LEFT');
        $this->where('A.IDCOLLECTPAYMENT', $idCollectPayment);
        $this->where('A.IDPARTNERTYPE', $idPartnerType);
        if($idPartnerType == 1) $this->where('A.IDVENDOR', $idVendor);
        if($idPartnerType == 2) $this->where('A.IDDRIVER', $idDriver);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return false;
        return $row;
	}
}
