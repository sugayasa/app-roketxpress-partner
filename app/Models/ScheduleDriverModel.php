<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleDriverModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 't_scheduledriver';
    protected $primaryKey       = 'IDSCHEDULEDRIVER';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['IDRESERVATIONDETAILS', 'IDDRIVER', 'USERINPUT', 'USERCONFIRM', 'DATETIMEINPUT', 'DATETIMECONFIRM', 'TIMESCHEDULE', 'STATUSPROCESS', 'STATUSCONFIRM', 'STATUS'];

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

    public function getDataScheduleReservation($idDriver, $scheduleDateStart, $scheduleDateEnd)
    {	
        $this->select("A.IDSCHEDULEDRIVER AS IDSCHEDULE, LEFT(D.RESERVATIONTIMESTART, 5) AS TIMESCHEDULE, B.SCHEDULEDATE, B.PRODUCTNAME, D.CUSTOMERNAME,
                       D.NUMBEROFADULT AS PAXADULT, D.NUMBEROFCHILD  AS PAXCHILD, A.STATUSPROCESS, IFNULL(E.STATUSPROCESSNAME, 'Scheduled') AS STATUSPROCESSNAME,
                       IF(COUNT(F.IDCOLLECTPAYMENT) > 0, 1, 0) AS STATUSINCLUDECOLLECT");
        $this->from('t_scheduledriver AS A', true);
        $this->join('t_reservationdetails AS B', 'A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS', 'LEFT');
        $this->join('t_reservationdetailsticket AS C', 'A.IDRESERVATIONDETAILS = C.IDRESERVATIONDETAILS', 'LEFT');
        $this->join('t_reservation AS D', 'B.IDRESERVATION = D.IDRESERVATION', 'LEFT');
        $this->join('m_statusprocessdriver AS E', 'A.STATUSPROCESS = E.IDSTATUSPROCESSDRIVER', 'LEFT');
        $this->join('t_collectpayment AS F', 'D.IDRESERVATION = F.IDRESERVATION AND F.IDDRIVER = '.$idDriver, 'LEFT');
        $this->where('A.IDDRIVER', $idDriver);
        $this->where('DATE(B.SCHEDULEDATE) >= ', $scheduleDateStart);
        $this->where('DATE(B.SCHEDULEDATE) <= ', $scheduleDateEnd);
        $this->where('A.STATUSCONFIRM', 1);
        $this->groupBy('A.IDSCHEDULEDRIVER');

        $result =   $this->get()->getResultObject();
        
        if(is_null($result)) return false;
        return $result;
	}

    public function getDetailScheduleReservation($idScheduleDriver, $idDriver)
    {	
        $this->select("E.SOURCENAME, D.BOOKINGCODE, D.RESERVATIONTITLE, D.CUSTOMERNAME, D.CUSTOMERCONTACT, D.CUSTOMEREMAIL, B.PRODUCTNAME, B.NOMINAL, B.NOTES,
                    D.NUMBEROFADULT AS PAXADULT, D.NUMBEROFCHILD  AS PAXCHILD, D.NUMBEROFINFANT  AS PAXINFANT, DATE_FORMAT(B.SCHEDULEDATE, '%d %b %Y') AS DATEACTIVITY, LEFT(D.RESERVATIONTIMESTART, 5) AS PICKUPTIME,
                    LEFT(D.RESERVATIONTIMESTART, 5) AS TIMESCHEDULE, IF(D.IDAREA = -1, 'Without Transfer', IFNULL(G.AREANAME, '-')) AS AREANAME, IF(D.IDAREA = -1, '-', IFNULL(G.AREATAGS, '-')) AS AREATAGS,
                    IF(D.PICKUPLOCATION IS NULL OR D.PICKUPLOCATION = '', '-', D.PICKUPLOCATION) AS PICKUPLOCATION, D.HOTELNAME, D.REMARK,  DATE_FORMAT(B.DATETIMEINPUT, '%d %b %Y %H:%i') AS DATETIMERECEPTION,
                    IFNULL(DATE_FORMAT(A.DATETIMECONFIRM, '%d %b %Y %H:%i'), '-') AS DATETIMECONFIRM, IF(A.USERCONFIRM IS NULL OR A.USERCONFIRM = '', '-', A.USERCONFIRM) AS USERCONFIRM, A.STATUSPROCESS,
                    IFNULL(H.STATUSPROCESSNAME, 'Scheduled') AS STATUSPROCESSNAME, H.ISFINISHED, IF(COUNT(F.IDCOLLECTPAYMENT) > 0, 1, 0) AS STATUSINCLUDECOLLECT, '0' AS TOTALAMOUNTIDRCOLLECTPAYMENT,
                    '0' AS STATUSCOLLECTPAYMENT, '-' AS DESCRIPTIONCOLLECTPAYMENT, B.SCHEDULEDATE, B.IDRESERVATION, A.IDRESERVATIONDETAILS");
        $this->from('t_scheduledriver AS A', true);
        $this->join('t_reservationdetails AS B', 'A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS', 'LEFT');
        $this->join('t_reservationdetailsticket AS C', 'A.IDRESERVATIONDETAILS = C.IDRESERVATIONDETAILS', 'LEFT');
        $this->join('t_reservation AS D', 'B.IDRESERVATION = D.IDRESERVATION', 'LEFT');
        $this->join('m_source AS E', 'D.IDSOURCE = E.IDSOURCE', 'LEFT');
        $this->join('t_collectpayment AS F', 'B.IDRESERVATION = F.IDRESERVATION AND F.IDDRIVER = '.$idDriver, 'LEFT');
        $this->join('m_area AS G', 'D.IDAREA = G.IDAREA', 'LEFT');
        $this->join('m_statusprocessdriver AS H', 'A.STATUSPROCESS = H.IDSTATUSPROCESSDRIVER', 'LEFT');
        $this->where('A.IDSCHEDULEDRIVER', $idScheduleDriver);
        $this->groupBy('A.IDSCHEDULEDRIVER');
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return false;
        return $row;
	}
}
