<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductListModel extends Model
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

    public function getDataProductList($keywordSearch, $idVendor)
    {	
        $this->select("B.PRODUCTNAME, A.MINPAX, A.MAXPAX, A.PRICEADULT, A.PRICECHILD, A.PRICEINFANT,
				A.NOTES");
        $this->from('t_vendorticketprice AS A', true);
        $this->join('m_product AS B', 'A.IDPRODUCT = B.IDPRODUCT', 'LEFT');
        $this->where('A.IDVENDOR', $idVendor);
        if(isset($keywordSearch) && $keywordSearch != "") $this->like('B.PRODUCTNAME', $keywordSearch)->orLike('A.NOTES', $keywordSearch);
        $this->orderBy('B.PRODUCTNAME, A.MINPAX');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
	}
}
