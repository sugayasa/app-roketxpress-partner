<?php

namespace App\Models;

use CodeIgniter\Model;

class MainOperation extends Model
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

    public function execQueryWithLimit($queryString, $page, $dataPerPage)
    {
		$startid    =	($page * 1 - 1) * $dataPerPage;
        $query      =   $this->query($queryString." LIMIT ".$startid.", ".$dataPerPage);

        return $query->getResult();
    }

    public function generateResultPagination($result, $basequery, $keyfield, $page, $dataperpage)
    {
        $startid	=	($page * 1 - 1) * $dataperpage;
		$datastart	=	$startid + 1;
		$dataend	=	$datastart + $dataperpage - 1;
		$query      =   $this->query("SELECT IFNULL(COUNT(".$keyfield."), 0) AS TOTAL FROM (".$basequery.") AS A");
		
		$row		=	$query->getRow();
		$datatotal	=	$row->TOTAL;
		$pagetotal	=	ceil($datatotal/$dataperpage);
		$datastart	=	$pagetotal == 0 ? 0 : $startid + 1;
		$startnumber=	$pagetotal == 0 ? 0 : ($page-1) * $dataperpage + 1;
		$dataend	=	$dataend > $datatotal ? $datatotal : $dataend;
		
		return array("data"=>$result ,"dataStart"=>$datastart, "dataEnd"=>$dataend, "dataTotal"=>$datatotal, "pageTotal"=>$pagetotal, "startNumber"=>$startnumber);
    }

	public function generateEmptyResult()
    {
		return array("data"=>[], "datastart"=>0, "dataend"=>0, "datatotal"=>0, "pagetotal"=>0);
	}

    public function insertDataTable($tableName, $arrInsert)
    {
        $db     =   \Config\Database::connect();
        try {
            $table  =   $db->table($tableName);
            foreach($arrInsert as $field => $value){
                $table->set($field, $value);
            }
            $table->insert();

            $insertID       =   $db->insertID();
            $affectedRows   =   $db->affectedRows();

            if($insertID > 0 || $affectedRows > 0) return ["status"=>true, "errCode"=>false, "insertID"=>$insertID];
            return ["status"=>false, "errCode"=>1329];
        } catch (\Throwable $th) {
            $error		    =	$db->error();
            $errorCode	    =	$error['code'] == 0 ? 1329 : $error['code'];
            return ["status"=>false, "errCode"=>$errorCode, "errorMessages"=>$th];
        }
    }

    public function updateDataTable($tableName, $arrUpdate, $arrWhere)
    {
        $db     =   \Config\Database::connect();
        try {
            $table  =   $db->table($tableName);
            foreach($arrUpdate as $field => $value){
                $table->set($field, $value);
            }

            foreach($arrWhere as $field => $value){
                if(is_array($value)){
                    $table->whereIn($field, $value);
                } else {
                    $table->where($field, $value);
                }
            }
            $table->update();

            $affectedRows   =   $db->affectedRows();
            if($affectedRows > 0) return ["status"=>true, "errCode"=>false];
            return ["status"=>false, "errCode"=>1329];
        } catch (\Throwable $th) {
            $error		    =	$db->error();
            $errorCode	    =	$error['code'] == 0 ? 1329 : $error['code'];
            return ["status"=>false, "errCode"=>$errorCode, "errorMessages"=>$th];
        }
        return ["status"=>false, "errCode"=>false];
    }

    public function deleteDataTable($tableName, $arrWhere)
    {
        $db     =   \Config\Database::connect();
        try {
            $table  =   $db->table($tableName);

            foreach($arrWhere as $field => $value){
                if(is_array($value)){
                    $table->whereIn($field, $value);
                } else {
                    $table->where($field, $value);
                }
            }
            $table->delete();

            $affectedRows   =   $db->affectedRows();
            if($affectedRows > 0) return ["status"=>true, "affectedRows"=>$affectedRows];
            return ["status"=>false, "errCode"=>1329];
        } catch (\Throwable $th) {
            $error		    =	$db->error();
            $errorCode	    =	$error['code'] == 0 ? 1329 : $error['code'];
            return ["status"=>false, "errCode"=>$errorCode, "errorMessages"=>$th];
        }
    }

    public function getVendorDetailsById($idVendor)
    {	
        $this->select("NAME, ADDRESS, PHONE, EMAIL, TRANSPORTSERVICE, FINANCESCHEMETYPE");
        $this->from('m_vendor', true);
        $this->where('IDVENDOR', $idVendor);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)){
            return [
                'NAME'              => '-',
                'ADDRESS'           => '-',
                'PHONE'             => '-',
                'EMAIL'             => '-',
                'TRANSPORTSERVICE'  => 0,
                'FINANCESCHEMETYPE' => 1
            ];
        }
        return $row;
	}

    public function getDriverDetailsById($idDriver)
    {	
        $this->select("NAME, ADDRESS, PHONE, EMAIL");
        $this->from('m_driver', true);
        $this->where('IDDRIVER', $idDriver);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)){
            return [
                'NAME'              => '-',
                'ADDRESS'           => '-',
                'PHONE'             => '-',
                'EMAIL'             => '-'
            ];
        }
        return $row;
	}

    public function getDataStatusProcess($idPartnerType, $idStatusProcess)
    {	
        $this->select("STATUSPROCESSNAME, ISFINISHED");
        if($idPartnerType == 1) $this->from('m_statusprocessvendor', true);
        if($idPartnerType == 2) $this->from('m_statusprocessdriver', true);
        if($idPartnerType == 1) $this->where('IDSTATUSPROCESSVENDOR', $idStatusProcess);
        if($idPartnerType == 2) $this->where('IDSTATUSPROCESSDRIVER', $idStatusProcess);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return ['STATUSPROCESSNAME' => '-', 'ISFINISHED' => 1];
        return $row;
	}

    public function getMaxStatusProcess($idPartnerType)
    {	
        if($idPartnerType == 1) $this->select("MAX(IDSTATUSPROCESSVENDOR) AS MAXSTATUSPROCESS");
        if($idPartnerType == 1) $this->from('m_statusprocessvendor', true);

        if($idPartnerType == 2) $this->select("MAX(IDSTATUSPROCESSDRIVER) AS MAXSTATUSPROCESS");
        if($idPartnerType == 2) $this->from('m_statusprocessdriver', true);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return 0;
        return $row['MAXSTATUSPROCESS'];
	}

    public function getCurrencyExchangeByDate($currency, $scheduleDate)
    {	
        $this->select('EXCHANGEVALUE');
        $this->from('t_currencyexchange', true);
        $this->where('CURRENCY', $currency);
        $this->where('DATESTART', $scheduleDate);
        $this->orderBy('DATESTART DESC');
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return 1;
        return $row['EXCHANGEVALUE'];
	}

    public function getPartnerDetail($idPartnerType, $idPartner)
    {
        $this->select("NAME, ADDRESS, PHONE, EMAIL, IFNULL(SECRETPIN, '') AS SECRETPIN, SECRETPINSTATUS,
                    IFNULL(DATE_FORMAT(SECRETPINLASTUPDATE, '%d %b %Y %H:%i'), '-') AS SECRETPINLASTUPDATE");
        if($idPartnerType == 1) $this->from('m_vendor', true);
        if($idPartnerType == 1) $this->where('IDVENDOR', $idPartner);

        if($idPartnerType == 2) $this->from('m_driver', true);
        if($idPartnerType == 2) $this->where('IDDRIVER', $idPartner);

         $row    =   $this->get()->getRowArray();

        if(!is_null($row)) return $row;
        return [
            'NAME'      =>  '-',
            'ADDRESS'   =>  '-',
            'PHONE'     =>  '-',
            'EMAIL'     =>  '-'
        ];
    }

    public function checkPINPartner($idPartnerType, $idPartner, $pinInput)
    {
        $this->select("SECRETPIN");
        if($idPartnerType == 1) $this->from('m_vendor', true);
        if($idPartnerType == 1) $this->where('IDVENDOR', $idPartner);

        if($idPartnerType == 2) $this->from('m_driver', true);
        if($idPartnerType == 2) $this->where('IDDRIVER', $idPartner);

         $row    =   $this->get()->getRowArray();

        if(!is_null($row) && $row['SECRETPIN'] == $pinInput){
            return true;
        }
        return false;
    }

    public function getTotalUnconfirmedReservation($idPartnerType, $idPartner)
    {
        $fieldCount =   $idPartnerType == 1 ? "IDSCHEDULEVENDOR" : "IDSCHEDULEDRIVER";
        $tableName  =   $idPartnerType == 1 ? "t_schedulevendor" : "t_scheduledriver";
        $fieldWhere =   $idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";

        $this->select("COUNT(".$fieldCount.") AS TOTALUNCONFIRMEDRESERVATION");
        $this->from($tableName, true);
        $this->where($fieldWhere, $idPartner);
        $this->where('STATUSCONFIRM', 0);

        $row    =   $this->get()->getRowArray();

        if(!is_null($row)) return $row['TOTALUNCONFIRMEDRESERVATION'];
        return 0;
    }

    public function getTotalActiveCollectPayment($idPartnerType, $idPartner)
    {
        $fieldWhere =   $idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";

        $this->select("COUNT(IDCOLLECTPAYMENT) AS TOTALACTIVECOLLECTPAYMENT");
        $this->from('t_collectpayment', true);
        $this->where($fieldWhere, $idPartner);
        $this->where('STATUS', 0);

        $row    =   $this->get()->getRowArray();

        if(!is_null($row)) return $row['TOTALACTIVECOLLECTPAYMENT'];
        return 0;
    }

    public function getTotalActiveWithdrawal($idPartnerType, $idPartner)
    {
        $fieldWhere =   $idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";

        $this->select("COUNT(IDWITHDRAWALRECAP) AS TOTALACTIVEWITHDRAWAL");
        $this->from('t_withdrawalrecap', true);
        $this->where($fieldWhere, $idPartner);
        $this->whereIn('STATUSWITHDRAWAL', [0,1]);

        $row    =   $this->get()->getRowArray();

        if(!is_null($row)) return $row['TOTALACTIVEWITHDRAWAL'];
        return 0;
    }
}
