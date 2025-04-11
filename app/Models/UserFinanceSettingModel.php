<?php

namespace App\Models;

use CodeIgniter\Model;

class UserFinanceSettingModel extends Model
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

    public function getDataListBankAccount($idPartnerType, $idPartner)
    {	
        $this->select("A.ACCOUNTNUMBER, A.ACCOUNTHOLDERNAME, B.BANKNAME, A.IDBANKACCOUNTPARTNER, A.IDBANK");
        $this->from('t_bankaccountpartner AS A', true);
        $this->join('m_bank AS B', 'A.IDBANK = B.IDBANK', 'LEFT');
        $this->where('A.IDPARTNERTYPE', $idPartnerType);
        $this->where('A.IDPARTNER', $idPartner);
        $this->where('A.STATUS !=', 1);

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
	}

    public function getDataListUserPartner($idPartnerType, $idPartner)
    {	
        $this->select("A.NAME, A.USERNAME, A.EMAIL, B.LEVELNAME, A.STATUS, IFNULL(DATE_FORMAT(A.LASTLOGIN, '%d %b %Y %H:%i'), '-') AS LASTLOGIN,
                    A.IDUSERPARTNER, A.IDUSERLEVELPARTNER, '' AS ALLOWEDITING");
        $this->from('m_userpartner AS A', true);
        $this->join('m_userlevelpartner AS B', 'A.IDUSERLEVELPARTNER = B.IDUSERLEVELPARTNER', 'LEFT');
        $this->where('A.IDPARTNERTYPE', $idPartnerType);
        if($idPartnerType == 1) $this->where('A.IDVENDOR', $idPartner);
        if($idPartnerType == 2) $this->where('A.IDDRIVER', $idPartner);

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
	}

    public function checkDataUsername($idUserPartner, $username)
    {	
        $this->select("IDUSERPARTNER, STATUS");
        $this->from('m_userpartner', true);
        $this->where('USERNAME', $username);
        $this->where('IDUSERPARTNER != ', $idUserPartner);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return false;
        return $row;
	}
}