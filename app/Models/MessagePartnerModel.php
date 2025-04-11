<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\MainOperation;

class MessagePartnerModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 't_messagepartner';
    protected $primaryKey       = 'IDMESSAGEPARTNER';
    protected $useAutoIncrement = true;
    protected $insertID         = 0;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['IDMESSAGEPARTNERTYPE', 'IDPARTNERTYPE', 'IDPARTNER', 'IDPRIMARY', 'TITLE', 'MESSAGE', 'DATETIMEINSERT', 'DATETIMEREAD'];

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
    
    public function getUnreadNotificationList($idPartnerType, $idVendor, $idDriver)
    {	
        $this->select("A.IDMESSAGEPARTNER, A.IDMESSAGEPARTNERTYPE, B.MESSAGEPARTNERTYPE, B.ICON, A.TITLE, A.MESSAGE, A.IDPRIMARY,
                    DATE_FORMAT(A.DATETIMEINSERT, '%d %b %Y %H:%i') AS DATETIMEINSERT");
        $this->from('t_messagepartner AS A', true);
        $this->join('m_messagepartnertype AS B', 'A.IDMESSAGEPARTNERTYPE = B.IDMESSAGEPARTNERTYPE', 'LEFT');
        $this->where('A.IDPARTNERTYPE', $idPartnerType);
        if($idPartnerType == 1) $this->where('A.IDPARTNER', $idVendor);
        if($idPartnerType == 2) $this->where('A.IDPARTNER', $idDriver);
        $this->where('A.DATETIMEREAD', '0000-00-00 00:00:00');
        $this->orderBy('A.DATETIMEINSERT DESC');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
	}
    
    public function getDataNotification($page, $dataPerPage, $status, $idMessagePartnerType, $keywordSearch, $idPartnerType, $idVendor, $idDriver)
    {	
        $mainOperation      =   new MainOperation();
		$con_idMessageType	=	isset($idMessagePartnerType) && $idMessagePartnerType != "" ? "A.IDMESSAGEPARTNERTYPE = ".$idMessagePartnerType : "1=1";
		$con_keywordSearch	=	isset($keywordSearch) && $keywordSearch != "" ? "(A.TITLE LIKE '%".$keywordSearch."%' OR A.MESSAGE LIKE '%".$keywordSearch."%')" : "1=1";
		$con_status         =	isset($status) && $status != 1 ? "A.DATETIMEREAD != '0000-00-00 00:00:00'" : "A.DATETIMEREAD = '0000-00-00 00:00:00'";
        $valuePartnerID     =   $idPartnerType == 1 ? $idVendor : $idDriver;
		$baseQuery			=	"SELECT A.IDMESSAGEPARTNER, A.IDMESSAGEPARTNERTYPE, B.MESSAGEPARTNERTYPE, B.ICON, A.TITLE, A.MESSAGE, A.IDPRIMARY,
										DATE_FORMAT(A.DATETIMEINSERT, '%d %b %Y %H:%i') AS DATETIMEINSERT
								FROM t_messagepartner A
								LEFT JOIN m_messagepartnertype B ON A.IDMESSAGEPARTNERTYPE = B.IDMESSAGEPARTNERTYPE
								WHERE ".$con_idMessageType." AND ".$con_keywordSearch." AND A.IDPARTNERTYPE = ".$idPartnerType." AND A.IDPARTNER = ".$valuePartnerID." AND ".$con_status."
								ORDER BY A.DATETIMEINSERT DESC";
        $result             =   $mainOperation->execQueryWithLimit($baseQuery, $page, $dataPerPage);

        if(is_null($result)) return $mainOperation->generateEmptyResult();
        return $mainOperation->generateResultPagination($result, $baseQuery, 'IDMESSAGEPARTNER', $page, $dataPerPage);
	}
}
