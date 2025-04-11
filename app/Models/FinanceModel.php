<?php

namespace App\Models;

use CodeIgniter\Model;

class FinanceModel extends Model
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

    public function getDataActiveBankAccountPartner($idPartnerType, $idPartner)
    {	
        $this->select("A.IDBANKACCOUNTPARTNER, A.IDBANK, A.ACCOUNTNUMBER, A.ACCOUNTHOLDERNAME, B.BANKNAME, CONCAT('".URL_BANK_LOGO."', B.BANKLOGO) AS BANKLOGO");
        $this->from('t_bankaccountpartner AS A', true);
        $this->join('m_bank AS B', 'A.IDBANK = B.IDBANK', 'LEFT');
        $this->where('A.IDPARTNERTYPE', $idPartnerType);
        $this->where('A.IDPARTNER', $idPartner);
        $this->where('A.STATUS', 1);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(is_null($row)) return false;
        return $row;
	}

    public function getDataRecapPerPartnerDetail($idPartnerType, $idPartner, $maxDateFinance)
    {
        $tableMaster        =   $idPartnerType == 1 ? "m_vendor" : "m_driver";
        $fieldWhereJoin     =   $idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";
        $fieldDepositBalance=   $idPartnerType == 1 ? "F.DEPOSITBALANCE" : "0";
        $fieldDepositLast   =   $idPartnerType == 1 ? "IFNULL(DATE_FORMAT(F.LASTDEPOSITTRANSACTION, '%d %b %Y'), '-')" : "'-'";
        $this->select("COUNT(C.IDFEE) AS TOTALSCHEDULE, IFNULL(SUM(C.FEENOMINAL), 0) AS TOTALFEE, IFNULL(D.TOTALCOLLECTPAYMENT, 0) AS TOTALCOLLECTPAYMENT,
            IFNULL(D.TOTALSCHEDULEWITHCOLLECTPAYMENT, 0) AS TOTALSCHEDULEWITHCOLLECTPAYMENT, IFNULL(DATE_FORMAT(E.LASTWITHDRAWALDATE, '%d %b %Y'), '-') AS LASTWITHDRAWALDATE,
            ".$fieldDepositBalance." AS DEPOSITBALANCE, ".$fieldDepositLast." AS LASTDEPOSITTRANSACTIONDATE");
        $this->from($tableMaster.' AS A', true);
        $this->join('t_fee AS C', "A.".$fieldWhereJoin." = C.".$fieldWhereJoin." AND C.WITHDRAWSTATUS = 0 AND C.IDWITHDRAWALRECAP = 0 AND C.DATESCHEDULE <= '".$maxDateFinance."'", 'LEFT');
        $this->join("(SELECT DA.".$fieldWhereJoin.", SUM(DB.AMOUNTIDR) AS TOTALCOLLECTPAYMENT, COUNT(DA.IDCOLLECTPAYMENT) AS TOTALSCHEDULEWITHCOLLECTPAYMENT
                FROM t_collectpayment AS DA
                LEFT JOIN t_reservationpayment AS DB ON DA.IDRESERVATIONPAYMENT = DB.IDRESERVATIONPAYMENT
                WHERE DA.".$fieldWhereJoin." = ".$idPartner." AND DA.STATUSSETTLEMENTREQUEST NOT IN (1,2) AND DA.DATECOLLECT <= '".$maxDateFinance."'
                GROUP BY DA.".$fieldWhereJoin.") AS D", "A.".$fieldWhereJoin." = D.".$fieldWhereJoin, 'LEFT');
        $this->join('(SELECT '.$fieldWhereJoin.', MAX(DATETIMEREQUEST) AS LASTWITHDRAWALDATE
                FROM t_withdrawalrecap
                WHERE '.$fieldWhereJoin.' = '.$idPartner.' AND STATUSWITHDRAWAL = 2
                GROUP BY '.$fieldWhereJoin.') AS E', 'A.'.$fieldWhereJoin.' = E.'.$fieldWhereJoin, 'LEFT');
        $this->join('(SELECT IDVENDOR, SUM(AMOUNT) AS DEPOSITBALANCE, MAX(DATETIMEINPUT) AS LASTDEPOSITTRANSACTION
                FROM t_depositvendorrecord
                WHERE IDVENDOR = '.$idPartner.'
                GROUP BY IDVENDOR) AS F', 'A.'.$fieldWhereJoin.' = F.IDVENDOR', 'LEFT');
        $this->where('A.'.$fieldWhereJoin, $idPartner);
        $this->groupBy('A.'.$fieldWhereJoin);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(!is_null($row)) return $row;
        return [
            'TOTALSCHEDULE'						=>	0,
            'TOTALFEE'							=>	0,
            'TOTALCOLLECTPAYMENT'				=>	0,
            'TOTALSCHEDULEWITHCOLLECTPAYMENT'	=>	0,
            'LASTWITHDRAWALDATE'				=>	'-'
        ];
	}

    public function getDataListFee($idPartnerType, $idPartner, $maxDateFinance)
    {	
        $this->select("DATE_FORMAT(A.DATESCHEDULE, '%d %b %Y') AS SCHEDULEDATE, LEFT(B.RESERVATIONTIMESTART, 5) AS RESERVATIONTIMESTART,
                    C.SOURCENAME, B.BOOKINGCODE, B.CUSTOMERNAME, A.RESERVATIONTITLE, A.JOBTITLE AS PRODUCTNAME, A.FEENOMINAL AS NOMINAL");
        $this->from('t_fee AS A', true);
        $this->join('t_reservation AS B', 'A.IDRESERVATION = B.IDRESERVATION', 'LEFT');
        $this->join('m_source AS C', 'B.IDSOURCE = C.IDSOURCE', 'LEFT');
        if($idPartnerType == 1) $this->where('A.IDVENDOR', $idPartner);
        if($idPartnerType == 2) $this->where('A.IDDRIVER', $idPartner);
        $this->where('A.WITHDRAWSTATUS', 0);
        $this->where('A.IDWITHDRAWALRECAP', 0);
        $this->where('A.DATESCHEDULE <= ', $maxDateFinance);
        $this->orderBy('A.DATESCHEDULE DESC');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
	}

    public function getDataListCollectPayment($idPartnerType, $idPartner, $maxDateFinance)
    {	
        $this->select("DATE_FORMAT(A.DATECOLLECT, '%d %b %Y') AS DATECOLLECT, B.INPUTTYPE, C.SOURCENAME, B.BOOKINGCODE, B.CUSTOMERNAME,
                    B.RESERVATIONTITLE, B.REMARK, D.DESCRIPTION, D.AMOUNTCURRENCY, D.AMOUNT, D.AMOUNTIDR");
        $this->from('t_collectpayment AS A', true);
        $this->join('t_reservation AS B', 'A.IDRESERVATION = B.IDRESERVATION', 'LEFT');
        $this->join('m_source AS C', 'B.IDSOURCE = C.IDSOURCE', 'LEFT');
        $this->join('t_reservationpayment AS D', 'A.IDRESERVATIONPAYMENT = D.IDRESERVATIONPAYMENT', 'LEFT');
        $this->where('A.IDPARTNERTYPE', $idPartnerType);
        $this->where('A.DATECOLLECT <=', $maxDateFinance);
        if($idPartnerType == 1) $this->where('A.IDVENDOR', $idPartner);
        if($idPartnerType == 2) $this->where('A.IDDRIVER', $idPartner);
        $this->whereNotIn('A.STATUSSETTLEMENTREQUEST', [1,2]);
        $this->orderBy('A.DATECOLLECT DESC');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
	}

    public function getDataListDepositHistory($idPartnerType, $idPartner, $startDateDeposit, $endDateDeposit)
    {	
        if($idPartnerType == 2) return false;
        $this->select("A.USERINPUT, DATE_FORMAT(A.DATETIMEINPUT, '%d %b %Y %H:%i') AS DATETIMEINPUTSTR, A.DESCRIPTION, 
                    IFNULL(D.SOURCENAME, '-') AS SOURCENAME, IFNULL(C.BOOKINGCODE, '-') AS BOOKINGCODE,
                    IFNULL(C.CUSTOMERNAME, '-') AS CUSTOMERNAME, IFNULL(C.RESERVATIONTITLE, '-') AS RESERVATIONTITLE,
                    IFNULL(F.DESCRIPTION, '-') AS PAYMENTDESCRIPTION, IFNULL(F.AMOUNTCURRENCY, '-') AS PAYMENTAMOUNTCURRENCY,
                    IFNULL(F.AMOUNT, '-') AS PAYMENTAMOUNT, IFNULL(F.EXCHANGECURRENCY, '-') AS PAYMENTEXCHANGECURRENCY,
                    IFNULL(F.AMOUNTIDR, '-') AS PAYMENTAMOUNTIDR, IFNULL(DATE_FORMAT(E.DATETIMESTATUS, '%d %b %Y %H:%i'), '-') AS COLLECTDATETIMESTATUS,
                    IFNULL(E.LASTUSERINPUT, '-') AS COLLECTUSERAPPROVE, A.AMOUNT, A.IDRESERVATIONDETAILS, A.IDCOLLECTPAYMENT,
                    IFNULL(CONCAT('".URL_TRANSFER_RECEIPT."', A.TRANSFERRECEIPT), '') AS TRANSFERRECEIPT");
        $this->from('t_depositvendorrecord AS A', true);
        $this->join('t_reservationdetails AS B', 'A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS', 'LEFT');
        $this->join('t_reservation AS C', 'B.IDRESERVATION = C.IDRESERVATION', 'LEFT');
        $this->join('m_source AS D', 'C.IDSOURCE = D.IDSOURCE', 'LEFT');
        $this->join('t_collectpayment AS E', 'A.IDCOLLECTPAYMENT = E.IDCOLLECTPAYMENT', 'LEFT');
        $this->join('t_reservationpayment AS F', 'E.IDRESERVATIONPAYMENT = F.IDRESERVATIONPAYMENT', 'LEFT');
        $this->where('A.IDVENDOR', $idPartner);
        $this->where('A.DATETIMEINPUT >=', $startDateDeposit);
        $this->where('A.DATETIMEINPUT <=', $endDateDeposit);
        $this->orderBy('A.DATETIMEINPUT');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
	}

    public function getDataListWithdrawHistory($idPartnerType, $idPartner, $startDateWithdrawal, $endDateWithdrawal)
    {	
        $this->select("A.IDWITHDRAWALRECAP, DATE_FORMAT(A.DATETIMEREQUEST, '%d %b %Y %H:%i') AS DATETIMEREQUEST, A.MESSAGE, CONCAT('".URL_BANK_LOGO."', B.BANKLOGO) AS BANKLOGO,
                        B.BANKNAME, A.ACCOUNTNUMBER, A.ACCOUNTHOLDERNAME, A.TOTALFEE, A.TOTALADDITIONALCOST, A.TOTALCOLLECTPAYMENT, A.TOTALPREPAIDCAPITAL, A.TOTALLOANCARINSTALLMENT,
                        A.TOTALLOANPERSONALINSTALLMENT, A.TOTALWITHDRAWAL, A.STATUSWITHDRAWAL");
        $this->from('t_withdrawalrecap AS A', true);
        $this->join('m_bank AS B', 'A.IDBANK = B.IDBANK', 'LEFT');
        if($idPartnerType == 1) $this->where('A.IDVENDOR', $idPartner);
        if($idPartnerType == 2) $this->where('A.IDDRIVER', $idPartner);
        $this->where('A.DATETIMEREQUEST >=', $startDateWithdrawal);
        $this->where('A.DATETIMEREQUEST <=', $endDateWithdrawal);
        $this->orderBy('A.DATETIMEREQUEST');

        $result =   $this->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
	}

    public function getDetailWithdrawal($idWithdrawalRecap)
    {	
        $this->select("DATE_FORMAT(A.DATETIMEREQUEST, '%d %b %Y %H:%i') AS DATETIMEREQUEST, A.MESSAGE, CONCAT('".URL_BANK_LOGO."', B.BANKLOGO) AS BANKLOGO,
                    B.BANKNAME, A.ACCOUNTNUMBER, A.ACCOUNTHOLDERNAME, A.TOTALFEE, A.TOTALADDITIONALCOST, A.TOTALCOLLECTPAYMENT, A.TOTALPREPAIDCAPITAL, A.TOTALLOANCARINSTALLMENT,
                    A.TOTALLOANPERSONALINSTALLMENT, A.TOTALWITHDRAWAL, IF(A.DATETIMEAPPROVAL = '0000-00-00 00:00:00', '-', DATE_FORMAT(A.DATETIMEAPPROVAL, '%d %b %Y %H:%i')) AS DATETIMEAPPROVAL,
                    IF(A.USERAPPROVAL IS NULL OR A.USERAPPROVAL = '', '-', A.USERAPPROVAL) AS USERAPPROVAL, A.STATUSWITHDRAWAL, 
                    IF(C.RECEIPTFILE IS NOT NULL AND C.RECEIPTFILE != '', CONCAT('".URL_HTML_TRANSFER_RECEIPT."', C.RECEIPTFILE), '') AS RECEIPTFILE");
        $this->from('t_withdrawalrecap AS A', true);
        $this->join('m_bank AS B', 'A.IDBANK = B.IDBANK', 'LEFT');
        $this->join('t_transferlist AS C', 'A.IDWITHDRAWALRECAP = C.IDWITHDRAWAL', 'LEFT');
        $this->where('A.IDWITHDRAWALRECAP', $idWithdrawalRecap);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(!is_null($row)) return $row;
        return false;
	}

    public function getListDetailWithdrawal($idWithdrawalRecap)
    {	
        $db                     =   $this->db;
        $unionAdditionalCost    =   $db->table("t_reservationadditionalcost")->select("2 AS TYPE, 'Additional Cost' AS TYPESTR, DATE(DATETIMEINPUT) AS DATEDB, DATE_FORMAT(DATETIMEINPUT, '%d %b %Y') AS DATESTR, IFNULL(DESCRIPTION, '-') AS DESCRIPTION, NOMINAL")->where("IDWITHDRAWALRECAP", $idWithdrawalRecap);
        $unionCollectPayment    =   $db->table("t_collectpayment AS A")->select("3 AS TYPE, 'Collect Payment' AS TYPESTR, DATE(A.DATECOLLECT) AS DATEDB, DATE_FORMAT(A.DATECOLLECT, '%d %b %Y') AS DATESTR, IFNULL(B.DESCRIPTION, '-') AS DESCRIPTION, (B.AMOUNTIDR * -1) AS NOMINAL")->join('t_reservationpayment B', 'A.IDRESERVATIONPAYMENT = B.IDRESERVATIONPAYMENT', 'LEFT')->where("A.IDWITHDRAWALRECAP", $idWithdrawalRecap);
        $unionPrepaidCapital    =   $db->table("t_withdrawalrecap")->select("4 AS TYPE, 'Prepaid Capital' AS TYPESTR, DATE(DATETIMEREQUEST) AS DATEDB, DATE_FORMAT(DATETIMEREQUEST, '%d %b %Y') AS DATESTR, 'Prepaid capital installment' AS DESCRIPTION, (TOTALPREPAIDCAPITAL * -1) AS NOMINAL")->where("IDWITHDRAWALRECAP", $idWithdrawalRecap)->where('TOTALPREPAIDCAPITAL > ', 0);
        $unionCarLoan           =   $db->table("t_withdrawalrecap")->select("5 AS TYPE, 'Loan Installment' AS TYPESTR, DATE(DATETIMEREQUEST) AS DATEDB, DATE_FORMAT(DATETIMEREQUEST, '%d %b %Y') AS DATESTR, 'Car loan installment' AS DESCRIPTION, (TOTALLOANCARINSTALLMENT * -1) AS NOMINAL")->where("IDWITHDRAWALRECAP", $idWithdrawalRecap)->where('TOTALLOANCARINSTALLMENT > ', 0);
        $unionPersonalLoan      =   $db->table("t_withdrawalrecap")->select("6 AS TYPE, 'Loan Installment' AS TYPESTR, DATE(DATETIMEREQUEST) AS DATEDB, DATE_FORMAT(DATETIMEREQUEST, '%d %b %Y') AS DATESTR, 'Personal loan installment' AS DESCRIPTION, (TOTALLOANPERSONALINSTALLMENT * -1) AS NOMINAL")->where("IDWITHDRAWALRECAP", $idWithdrawalRecap)->where('TOTALLOANPERSONALINSTALLMENT > ', 0);
        $builderQuery           =   $db->table("t_fee")->select("1 AS TYPE, 'Fee' AS TYPESTR, DATESCHEDULE AS DATEDB, DATE_FORMAT(DATESCHEDULE, '%d %b %Y') AS DATESTR, JOBTITLE AS DESCRIPTION, FEENOMINAL AS NOMINAL")->where("IDWITHDRAWALRECAP", $idWithdrawalRecap)->unionAll($unionAdditionalCost)->unionAll($unionCollectPayment)->unionAll($unionPrepaidCapital)->unionAll($unionCarLoan)->unionAll($unionPersonalLoan);
        $result                 =   $db->newQuery()->fromSubquery($builderQuery, 'q')->orderBy('DATEDB ASC, TYPE ASC')->get()->getResultObject();

        if(is_null($result)) return false;
        return $result;
	}

    public function getTotalUnconfirmedCollectPayment($idPartnerType, $idPartner, $maxDateFinance)
    {
        $this->select("COUNT(IDCOLLECTPAYMENT) AS TOTALUNCONFIRMEDCOLLECTPAYMENT");
        $this->from('t_collectpayment', true);
        $this->where('IDPARTNERTYPE', $idPartnerType);
        $this->where('DATECOLLECT <=', $maxDateFinance);
        $this->where('STATUS', 0);
        if($idPartnerType == 1) $this->where('IDVENDOR', $idPartner);
        if($idPartnerType == 2) $this->where('IDDRIVER', $idPartner);
        $this->groupBy('IDPARTNERTYPE');
        $this->limit(1);

        $row    =   $this->get()->getRowArray();

        if(!is_null($row)) return $row['TOTALUNCONFIRMEDCOLLECTPAYMENT'];
        return 0;
	}

    public function getTotalUnfinishedSchedule($idPartnerType, $idPartner, $maxDateFinance)
    {
        if($idPartnerType == 1) return 0;
        $tableSchdule   =   $idPartnerType == 1 ? "t_schedulevendor" : "t_scheduledriver";
        $fieldWhereGroup=   $idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";

        $this->select("COUNT(A.IDRESERVATIONDETAILS) AS TOTALUNFINISHEDSCHEDULE");
        $this->from('t_reservationdetails AS A', true);
        $this->join($tableSchdule.' AS B', 'A.IDRESERVATIONDETAILS = B.IDRESERVATIONDETAILS', 'LEFT');
        $this->where('B.'.$fieldWhereGroup, $idPartner);
        $this->where('A.STATUS', 1);
        $this->where('B.STATUS !=', 3);
        $this->where('A.SCHEDULEDATE <=', $maxDateFinance);
        $this->groupBy('B.'.$fieldWhereGroup);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();
        if(!is_null($row)) return $row['TOTALUNFINISHEDSCHEDULE'];
        return 0;
	}

    public function getDataActiveWithdrawal($idPartnerType, $idPartner)
    {
        $this->select("A.TOTALWITHDRAWAL, A.MESSAGE, B.BANKNAME, A.ACCOUNTNUMBER, A.ACCOUNTHOLDERNAME");
        $this->from('t_withdrawalrecap AS A', true);
        $this->join('m_bank AS B', 'A.IDBANK = B.IDBANK', 'LEFT');
        $this->where('A.STATUSWITHDRAWAL', 0);
        if($idPartnerType == 1) $this->where('A.IDVENDOR', $idPartner);
        if($idPartnerType == 2) $this->where('A.IDDRIVER', $idPartner);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();
        if(!is_null($row)) return $row;
        return false;
	}

    public function getTotalWithdrawalRequest($idPartnerType)
    {
        $this->select("COUNT(IDWITHDRAWALRECAP) AS TOTALWITHDRAWALREQUEST");
        $this->from('t_withdrawalrecap', true);
        $this->where('STATUSWITHDRAWAL', 0);
        if($idPartnerType == 1) $this->where('IDVENDOR !=', 0);
        if($idPartnerType == 2) $this->where('IDDRIVER !=', 0);
        $this->limit(1);

        $row    =   $this->get()->getRowArray();
        if(!is_null($row)) return $row['TOTALWITHDRAWALREQUEST'];
        return 0;
	}
}
