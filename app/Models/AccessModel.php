<?php

namespace App\Models;

use CodeIgniter\Model;
use PHPUnit\Framework\Constraint\IsNull;

class AccessModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'm_userpartner';
    protected $primaryKey       = 'IDUSERPARTNER';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['IDUSERLEVELPARTNER', 'IDPARTNERTYPE', 'IDVENDOR', 'IDDRIVER', 'NAME', 'EMAIL', 'USERNAME', 'PASSWORD', 'HWID', 'LASTLOGIN', 'TOKENEXPIRED', 'STATUS'];

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

    public function checkHwidUserPartner($idUserPartner, $hwid)
    {
        $this->select('IDUSERPARTNER');
        $this->from('m_userpartner', true);
        $this->where('IDUSERPARTNER', $idUserPartner);
        $this->where('HWID', $hwid);

        if(is_null($this->get()->getRowArray())) return false;
        return true;
    }

    public function getUserPartnerDetail($idUserPartner)
    {
        $this->select('A.HWID, A.IDUSERLEVELPARTNER, A.NAME, A.EMAIL, B.LEVELNAME, IF(A.IDPARTNERTYPE = 1, C.NAME, D.NAME) AS PARTNERNAME,
                    IF(A.IDVENDOR != 0, C.TRANSPORTSERVICE, 1) AS TRANSPORTSERVICE, IF(A.IDPARTNERTYPE = 1, C.FINANCESCHEMETYPE, 1) AS FINANCESCHEMETYPE,
                    IF(A.IDPARTNERTYPE = 1, C.RTDBREFCODE, D.RTDBREFCODE) AS RTDBREFCODE');
        $this->from('m_userpartner AS A', true);
        $this->join('m_userlevelpartner AS B', 'A.IDUSERLEVELPARTNER = B.IDUSERLEVELPARTNER', 'LEFT');
        $this->join('m_vendor AS C', 'A.IDVENDOR = C.IDVENDOR', 'LEFT');
        $this->join('m_driver AS D', 'A.IDDRIVER = D.IDDRIVER', 'LEFT');
        $this->where('A.IDUSERPARTNER', $idUserPartner);

        return $this->get()->getRowArray();
    }

    public function getPartnerMenu($idUserLevelPartner)
    {
        $this->select('B.GROUPNAME, B.DISPLAYNAME, B.MENUALIAS, B.URL, B.ICON');
        $this->from('m_menulevelpartner AS A', true);
        $this->join('m_menupartner AS B', 'A.IDMENUPARTNER = B.IDMENUPARTNER', 'LEFT');
        $this->where('A.IDUSERLEVELPARTNER', $idUserLevelPartner);
        $this->where('A.OPEN', 1);

        return $this->get()->getResultObject();
    }

    public function getPartnerGroupMenu($idUserLevelPartner)
    {
        $this->select('B.GROUPNAME');
        $this->from('m_menulevelpartner AS A', true);
        $this->join('m_menupartner AS B', 'A.IDMENUPARTNER = B.IDMENUPARTNER', 'LEFT');
        $this->where('A.IDUSERLEVELPARTNER', $idUserLevelPartner);
        $this->where('A.OPEN', 1);
        $this->groupBy('B.GROUPNAME');
        $this->having('COUNT(B.IDMENUPARTNER)', '> 1');
        $this->orderBy('B.ORDERGROUP');

        return $this->get()->getResultObject();
    }

    public function getListNotificationTypeUserLevelPartner($idUserLevelPartner)
    {
        $this->select('NOTIFSCHEDULE, NOTIFFINANCE');
        $this->from('m_userlevelpartner', true);
        $this->where('IDUSERLEVELPARTNER', $idUserLevelPartner);
        $result =   $this->get()->getRowArray();

        if(is_null($result)){
            return array(
                "NOTIFSCHEDULE" =>	0,
                "NOTIFFINANCE"  =>	0
            );
        }

        return $result;
    }

    public function getDataMessagePartnerType($idPartnerType)
    {
        $this->select('IDMESSAGEPARTNERTYPE AS ID, MESSAGEPARTNERTYPE AS VALUE');
        if($idPartnerType == 1) $this->where('FLAGVENDOR', 1);
        if($idPartnerType == 2) $this->where('FLAGDRIVER', 1);
        $this->from('m_messagepartnertype', true);
        $this->orderBy('MESSAGEPARTNERTYPE');

        return $this->get()->getResultObject();
    }

    public function getDataBank()
    {
        $this->select('IDBANK AS ID, BANKNAME AS VALUE');
        $this->from('m_bank', true);
        $this->orderBy('BANKNAME');

        return $this->get()->getResultObject();
    }

    public function getDataUserLevel()
    {
        $this->select('IDUSERLEVELPARTNER AS ID, LEVELNAME AS VALUE');
        $this->from('m_userlevelpartner', true);
        $this->orderBy('LEVELNAME');

        return $this->get()->getResultObject();
    }

    public function getDataUserLevelMenu()
    {
        $this->select('A.IDUSERLEVELPARTNER AS ID, C.DISPLAYNAME AS VALUE');
        $this->from('m_menulevelpartner AS A', true);
        $this->join('m_userlevelpartner AS B', 'A.IDUSERLEVELPARTNER = B.IDUSERLEVELPARTNER', 'LEFT');
        $this->join('m_menupartner AS C', 'A.IDMENUPARTNER = C.IDMENUPARTNER', 'LEFT');
        $this->orderBy('A.IDUSERLEVELPARTNER, C.ORDERGROUP, C.ORDERMENU');

        return $this->get()->getResultObject();
    }

    public function getDataTotalReservationVendor($yearMonth, $lastYearMonth, $idVendor)
    {
		$today		=   date('Y-m-d');
		$tomorrow	=   date('Y-m-d', strtotime("+1 days"));
        $this->select("COUNT(A.IDRESERVATION) AS TOTALRESERVATIONALLTIME,
                    IFNULL(SUM(IF(LEFT(B.RESERVATIONDATESTART, 7) = '".$yearMonth."', 1, 0)), 0) AS TOTALRESERVATIONTHISMONTH, 
                    IFNULL(SUM(IF(LEFT(B.RESERVATIONDATESTART, 7) = '".$lastYearMonth."', 1, 0)), 0) AS TOTALRESERVATIONLASTMONTH, 
                    IFNULL(SUM(IF(B.RESERVATIONDATESTART = '".$today."', 1, 0)), 0) AS TOTALRESERVATIONTODAY, 
                    IFNULL(SUM(IF(B.RESERVATIONDATESTART = '".$tomorrow."', 1, 0)), 0) AS TOTALRESERVATIONTOMORROW,
                    MIN(B.RESERVATIONDATESTART) AS MINRESERVATIONDATE");
        $this->from('t_reservationdetails AS A', true);
        $this->join('t_reservation AS B', 'A.IDRESERVATION = B.IDRESERVATION', 'LEFT');
        $this->where('A.IDVENDOR', $idVendor);
        $this->where('A.STATUS', 1);

        return $this->get()->getRowArray();
    }

    public function getDataTotalReservationDriver($yearMonth, $lastYearMonth, $idDriver)
    {
		$today		=   date('Y-m-d');
		$tomorrow	=   date('Y-m-d', strtotime("+1 days"));
        $this->select("COUNT(A.IDSCHEDULEDRIVER) AS TOTALRESERVATIONALLTIME,
                    IFNULL(SUM(IF(LEFT(B.SCHEDULEDATE, 7) = '".$yearMonth."', 1, 0)), 0) AS TOTALRESERVATIONTHISMONTH, 
                    IFNULL(SUM(IF(LEFT(B.SCHEDULEDATE, 7) = '".$lastYearMonth."', 1, 0)), 0) AS TOTALRESERVATIONLASTMONTH, 
                    IFNULL(SUM(IF(B.SCHEDULEDATE = '".$today."', 1, 0)), 0) AS TOTALRESERVATIONTODAY, 
                    IFNULL(SUM(IF(B.SCHEDULEDATE = '".$tomorrow."', 1, 0)), 0) AS TOTALRESERVATIONTOMORROW,
                    MIN(B.SCHEDULEDATE) AS MINRESERVATIONDATE");
        $this->from('t_scheduledriver AS A', true);
        $this->join('t_reservationdetails AS B', 'A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS', 'LEFT');
        $this->where('A.IDDRIVER', $idDriver);
        $this->where('B.STATUS', 1);

        return $this->get()->getRowArray();
    }
	
	public function getDataTopProductVendor($yearMonth, $totalMonth, $lastDateYearMonth, $idVendor)
    {	
        $this->select("PRODUCTNAME, IFNULL(CEILING(COUNT(IDRESERVATIONDETAILS) / ".$totalMonth."), 0) AS AVERAGERESERVATIONPERMONTH,
                    SUM(IF(LEFT(SCHEDULEDATE, 7) = '".$yearMonth."', 1, 0)) AS TOTALRESERVATIONOFMONTH");
        $this->from('t_reservationdetails', true);
        $this->where('SCHEDULEDATE <=', $lastDateYearMonth);
        $this->where('IDVENDOR', $idVendor);
        $this->where('STATUS', 1);
        $this->groupBy('PRODUCTNAME');
        $this->orderBy('AVERAGERESERVATIONPERMONTH DESC, TOTALRESERVATIONOFMONTH DESC');
        $this->limit(5);

        return $this->get()->getResultObject();		
	}

	public function getDataTopProductDriver($yearMonth, $totalMonth, $lastDateYearMonth, $idDriver)
    {	
        $this->select("B.PRODUCTNAME, IFNULL(CEILING(COUNT(A.IDSCHEDULEDRIVER) / ".$totalMonth."), 0) AS AVERAGERESERVATIONPERMONTH,
                    SUM(IF(LEFT(B.SCHEDULEDATE, 7) = '".$yearMonth."', 1, 0)) AS TOTALRESERVATIONOFMONTH");
        $this->from('t_scheduledriver AS A', true);
        $this->join('t_reservationdetails AS B', 'A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS', 'LEFT');
        $this->where('A.IDDRIVER', $idDriver);
        $this->where('B.SCHEDULEDATE <=', $lastDateYearMonth);
        $this->where('B.STATUS', 1);
        $this->groupBy('B.PRODUCTNAME');
        $this->orderBy('AVERAGERESERVATIONPERMONTH DESC, TOTALRESERVATIONOFMONTH DESC');
        $this->limit(5);

        return $this->get()->getResultObject();		
	}

    public function getDataGraphReservationVendor($yearMonth, $idVendor)
    {	
        $this->select("SCHEDULEDATE, COUNT(IDRESERVATIONDETAILS) AS TOTALRESERVATION");
        $this->from('t_reservationdetails', true);
        $this->where('LEFT(SCHEDULEDATE, 7)', $yearMonth);
        $this->where('IDVENDOR', $idVendor);
        $this->where('STATUS', 1);
        $this->groupBy('SCHEDULEDATE');
        $this->orderBy('SCHEDULEDATE');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
	}

    public function getDataGraphReservationDriver($yearMonth, $idDriver)
    {	
        $this->select("B.SCHEDULEDATE, COUNT(A.IDRESERVATIONDETAILS) AS TOTALRESERVATION");
        $this->from('t_scheduledriver AS A', true);
        $this->join('t_reservationdetails AS B', 'A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS', 'LEFT');
        $this->where('LEFT(B.SCHEDULEDATE, 7)', $yearMonth);
        $this->where('A.IDDRIVER', $idDriver);
        $this->where('B.STATUS', 1);
        $this->groupBy('B.SCHEDULEDATE');
        $this->orderBy('B.SCHEDULEDATE');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
	}    
}
