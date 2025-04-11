<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\MainOperation;

class FeeModel extends Model
{    
    protected $DBGroup          = 'default';
    protected $table            = 't_fee';
    protected $primaryKey       = 'IDFEE';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['DATESCHEDULE', 'RESERVATIONTITLE', 'JOBTITLE', 'FEENOMINAL', 'FEENOTES', 'CORRECTIONNOTES', 'USERAPPROVAL', 'DATETIMEINPUT', 'DATETIMEAPPROVAL', 'WITHDRAWSTATUS'];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    public function getDataDetailFee($page = 1, $dataPerPage = 25, $orderByStr, $orderType, $idPartnerType, $idPartner, $startDateStr, $endDateStr, $bookingCodeKeyword, $productNameKeyword)
    {	
        $mainOperation      =   new MainOperation();
        $con_likeProductName=   isset($productNameKeyword) && $productNameKeyword != "" ? "A.JOBTITLE LIKE '%".$productNameKeyword."%'" : "1=1";
        $con_likeBookingCode=   isset($bookingCodeKeyword) && $bookingCodeKeyword != "" ? "C.BOOKINGCODE LIKE '%".$bookingCodeKeyword."%'" : "1=1";
        $fieldAdult         =   $idPartnerType == 1 ? "E.PAXADULT" : "C.NUMBEROFADULT";
        $fieldChild         =   $idPartnerType == 1 ? "E.PAXCHILD" : "C.NUMBEROFCHILD";
        $fieldInfant        =   $idPartnerType == 1 ? "E.PAXINFANT" : "C.NUMBEROFINFANT";
        $fieldWhere         =   $idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";
        $baseQuery          =   "SELECT A.IDRESERVATIONDETAILS, DATE_FORMAT(B.SCHEDULEDATE, '%d %b %Y') AS SCHEDULEDATE, D.SOURCENAME, C.BOOKINGCODE, C.RESERVATIONTITLE, C.CUSTOMERNAME,
                                    A.JOBTITLE, A.FEENOTES, A.FEENOMINAL, ".$fieldAdult." AS PAXADULT, ".$fieldChild." AS PAXCHILD, ".$fieldInfant." AS PAXINFANT, E.PRICEPERPAXADULT,
                                    E.PRICEPERPAXCHILD, E.PRICEPERPAXINFANT, E.PRICETOTALADULT, E.PRICETOTALCHILD, E.PRICETOTALINFANT
                                FROM t_fee A
                                LEFT JOIN t_reservationdetails B ON A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS
                                LEFT JOIN t_reservation C ON B.IDRESERVATION = C.IDRESERVATION
                                LEFT JOIN m_source D ON C.IDSOURCE = D.IDSOURCE
                                LEFT JOIN t_reservationdetailsticket E ON A.IDRESERVATIONDETAILS = E.IDRESERVATIONDETAILS
                                WHERE B.SCHEDULEDATE BETWEEN '".$startDateStr."' AND'".$endDateStr."' AND B.STATUS = 1 AND A.".$fieldWhere." = ".$idPartner." AND ".$con_likeProductName." AND ".$con_likeBookingCode."
                                ORDER BY ".$orderByStr." ".$orderType;
        $result                 =   $mainOperation->execQueryWithLimit($baseQuery, $page, $dataPerPage);

        if(is_null($result)) return $mainOperation->generateEmptyResult();
        return $mainOperation->generateResultPagination($result, $baseQuery, 'IDRESERVATIONDETAILS', $page, $dataPerPage);
	}
}
