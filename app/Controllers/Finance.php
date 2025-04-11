<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Config\Services;
use CodeIgniter\I18n\Time;
use App\Models\MainOperation;
use App\Models\FinanceModel;
use Kreait\Firebase\Factory;

class Finance extends ResourceController
{
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    use ResponseTrait;
    protected $userData, $currentDateTime;
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger) {
        parent::initController($request, $response, $logger);

        try {
            $this->userData         =   $request->userData;
            $this->currentDateTime  =   $request->currentDateTime;
        } catch (\Throwable $th) {
        }
    }

    public function index()
    {
        return $this->failForbidden('[E-AUTH-000] Forbidden Access');
    }

    public function getDetailFinancePartner()
    {
        helper(['form']);
        $rules          =   [
            'maxDateFinance'        => ['label' => 'Maximum Date for Finance Calculations ', 'rules' => 'required|valid_date[d-m-Y]'],
            'startDateDeposit'      => ['label' => 'Start Date Deposit History', 'rules' => 'required|valid_date[d-m-Y]'],
            'endDateDeposit'        => ['label' => 'End Date Deposit History', 'rules' => 'required|valid_date[d-m-Y]'],
            'startDateWithdrawal'   => ['label' => 'Start Date Withdraw', 'rules' => 'required|valid_date[d-m-Y]'],
            'endDateWithdrawal'     => ['label' => 'End Date Withdraw', 'rules' => 'required|valid_date[d-m-Y]']
        ];

        if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());

        $mainOperation      =   new MainOperation();
        $financeModel       =   new FinanceModel();
        $maxDateFinance     =   $this->request->getVar('maxDateFinance');
        $maxDateFinance     =   isset($maxDateFinance) && $maxDateFinance != "" ? Time::createFromFormat('d-m-Y', $maxDateFinance)->toDateString() : "";
        $maxDateFinance     =   $maxDateFinance == "" ? date('Y-m-d') : $maxDateFinance;
        $startDateDeposit   =   $this->request->getVar('startDateDeposit');
        $startDateDeposit   =   isset($startDateDeposit) && $startDateDeposit != "" ? Time::createFromFormat('d-m-Y', $startDateDeposit)->toDateString() : "";
        $endDateDeposit     =   $this->request->getVar('endDateDeposit');
        $endDateDeposit     =   isset($endDateDeposit) && $endDateDeposit != "" ? Time::createFromFormat('d-m-Y', $endDateDeposit)->toDateString() : "";
        $startDateWithdrawal=   $this->request->getVar('startDateWithdrawal');
        $startDateWithdrawal=   isset($startDateWithdrawal) && $startDateWithdrawal != "" ? Time::createFromFormat('d-m-Y', $startDateWithdrawal)->toDateString() : "";
        $endDateWithdrawal  =   $this->request->getVar('endDateWithdrawal');
        $endDateWithdrawal  =   isset($endDateWithdrawal) && $endDateWithdrawal != "" ? Time::createFromFormat('d-m-Y', $endDateWithdrawal)->toDateString() : "";
        $idVendor           =   $this->userData->idVendor;
        $idDriver           =   $this->userData->idDriver;
        $idPartnerType      =   $this->userData->idPartnerType;
        $idPartner          =   $idPartnerType == 1 ? $idVendor : $idDriver;
        $isDriver           =   $idPartnerType == 1 ? false : true;
		$detailPartner		=	$mainOperation->getPartnerDetail($idPartnerType, $idPartner);
		$initialName        =	preg_match_all('/\b\w/', $detailPartner['NAME'], $matches);
		$initialName        =	implode('', $matches[0]);

		$dataBankAccount				    =	$financeModel->getDataActiveBankAccountPartner($idPartnerType, $idPartner);
		$dataRecapPerPartner                =	$financeModel->getDataRecapPerPartnerDetail($idPartnerType, $idPartner, $maxDateFinance);
		$dataListFee					    =	$financeModel->getDataListFee($idPartnerType, $idPartner, $maxDateFinance);
		$dataListCollectPayment			    =	$financeModel->getDataListCollectPayment($idPartnerType, $idPartner, $maxDateFinance);
		$dataListDepositHistory			    =	$financeModel->getDataListDepositHistory($idPartnerType, $idPartner, $startDateDeposit, $endDateDeposit);
		$dataListWithdrawHistory            =	$financeModel->getDataListWithdrawHistory($idPartnerType, $idPartner, $startDateWithdrawal, $endDateWithdrawal);
		$totalUnconfirmedCollectPayment     =	$financeModel->getTotalUnconfirmedCollectPayment($idPartnerType, $idPartner, $maxDateFinance);
		$totalUnfinishedSchedule            =	$financeModel->getTotalUnfinishedSchedule($idPartnerType, $idPartner, $maxDateFinance);
		$dataActiveWithdrawal               =	$financeModel->getDataActiveWithdrawal($idPartnerType, $idPartner);
        $detailPartner['INITIALNAME']       =   $initialName;
        $detailPartner['FINANCESCHEMETYPE'] =   $this->userData->financeSchemeType;
        $partnerSecretPINStatus             =   $detailPartner['SECRETPINSTATUS'];
        $withdrawalBalance                  =   $dataRecapPerPartner['TOTALFEE'] - $dataRecapPerPartner['TOTALCOLLECTPAYMENT'];
        $allowWithdrawal                    =   true;
        $msgDetentionWithdrawal             =   '';

        if($dataListDepositHistory){
            foreach($dataListDepositHistory as $keyListDepositHistory){
                $keyListDepositHistory->IDRESERVATIONDETAILS=   $keyListDepositHistory->IDRESERVATIONDETAILS == 0 ? "" : hashidEncode($keyListDepositHistory->IDRESERVATIONDETAILS);
                $keyListDepositHistory->IDCOLLECTPAYMENT    =   $keyListDepositHistory->IDCOLLECTPAYMENT == 0 ? "" : hashidEncode($keyListDepositHistory->IDCOLLECTPAYMENT);
            }
        }

        if($dataListWithdrawHistory){
            foreach($dataListWithdrawHistory as $keyListWithdrawHistory){
                $keyListWithdrawHistory->IDWITHDRAWALRECAP  =   $keyListWithdrawHistory->IDWITHDRAWALRECAP == 0 ? "" : hashidEncode($keyListWithdrawHistory->IDWITHDRAWALRECAP);
            }
        }

        if($dataBankAccount == false){
			$allowWithdrawal        =	false;
			$msgDetentionWithdrawal =	"Withdrawal is not allowed. Please <b>set up your bank account</b> first in the profile section";
		}
        unset($dataBankAccount['IDBANKACCOUNTPARTNER']);
        unset($dataBankAccount['IDBANK']);
		
		if($partnerSecretPINStatus != 2){
			$allowWithdrawal        =	false;
			$msgDetentionWithdrawal =	"Withdrawal is not allowed. Please set your secret PIN first in the <b>User & Finance Setting</b> section";
		}
		
		if($withdrawalBalance <= 0){
			$allowWithdrawal        =	false;
			$msgDetentionWithdrawal =	"Withdrawal is not allowed. Your <b>balance is insufficien</b>t to withdraw";
		}
		
		if($totalUnconfirmedCollectPayment > 0){
			$allowWithdrawal        =	false;
			$msgDetentionWithdrawal =	"Withdrawal is not allowed. Please <b>confirm all collect payments</b> that you have";
		}
		
		if($totalUnfinishedSchedule > 0){
			$allowWithdrawal        =	false;
			$dateYesterday          =	date('d M Y', strtotime("-1 days"));
			$msgDetentionWithdrawal =	"Withdrawal is not allowed. Please <b>update all your work (until ".$dateYesterday.")</b> schedules to finish";
		}
		
		if($dataActiveWithdrawal != false){
			$allowWithdrawal        =	false;
			$msgDetentionWithdrawal =	"Withdrawal is not allowed. Please <b>wait for the previous withdrawal process to complete</b>";
		}

        return $this->setResponseFormat('json')
                    ->respond([
                        "isDriver"                  =>  $isDriver,
                        "detailPartner"             =>  $detailPartner,
                        "dataBankAccount"           =>  $dataBankAccount,
                        "dataRecapPerPartner"       =>  $dataRecapPerPartner,
                        "dataListFee"               =>  $dataListFee,
                        "dataListCollectPayment"    =>  $dataListCollectPayment,
                        "dataListDepositHistory"    =>  $dataListDepositHistory,
                        "dataListWithdrawHistory"   =>  $dataListWithdrawHistory,
                        "allowWithdrawal"           =>  $allowWithdrawal,
                        "msgDetentionWithdrawal"    =>  $msgDetentionWithdrawal
                     ]);
    }

    public function getDataListDepositHistory()
    {
        helper(['form']);
        $rules          =   [
            'startDateDeposit'   => ['label' => 'Start Date Period', 'rules' => 'required|valid_date[d-m-Y]'],
            'endDateDeposit'     => ['label' => 'End Date Period', 'rules' => 'required|valid_date[d-m-Y]']
        ];

        if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());

        $financeModel           =   new FinanceModel();
        $startDateDeposit       =   $this->request->getVar('startDateDeposit');
        $startDateDeposit       =   isset($startDateDeposit) && $startDateDeposit != "" ? Time::createFromFormat('d-m-Y', $startDateDeposit)->toDateString() : "";
        $endDateDeposit         =   $this->request->getVar('endDateDeposit');
        $endDateDeposit         =   isset($endDateDeposit) && $endDateDeposit != "" ? Time::createFromFormat('d-m-Y', $endDateDeposit)->toDateString() : "";
        $idVendor               =   $this->userData->idVendor;
        $idDriver               =   $this->userData->idDriver;
        $idPartnerType          =   $this->userData->idPartnerType;
        $idPartner              =   $idPartnerType == 1 ? $idVendor : $idDriver;
		$dataListDepositHistory =	$financeModel->getDataListDepositHistory($idPartnerType, $idPartner, $startDateDeposit, $endDateDeposit);

        if(!$dataListDepositHistory){
            return throwResponseNotFound("No data found in the period you selected");
        }

        return $this->setResponseFormat('json')
                    ->respond([
                        "dataListDepositHistory"    =>  $dataListDepositHistory
                     ]);
    }

    public function getDetailWithdrawal()
    {
        helper(['form']);
        $rules          =   [
            'idWithdrawalRecap' => ['label' => 'Id Withdrawal Recap', 'rules' => 'required|alpha_numeric']
        ];

        if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());

        $financeModel           =   new FinanceModel();
        $idWithdrawalRecap      =   $this->request->getVar('idWithdrawalRecap');
        $idWithdrawalRecap      =   hashidDecode($idWithdrawalRecap);
		$detailWithdrawalRequest=	$financeModel->getDetailWithdrawal($idWithdrawalRecap);

        if(!$idWithdrawalRecap){
            return throwResponseNotFound("No data details found");
        }

		$listDetailWithdrawal	=	$financeModel->getListDetailWithdrawal($idWithdrawalRecap);
        return $this->setResponseFormat('json')
                    ->respond([
                        "detailWithdrawalRequest"   =>  $detailWithdrawalRequest,
                        "listDetailWithdrawal"      =>  $listDetailWithdrawal
                     ]);
    }

    public function submitWithdrawalRequest()
    {
        helper(['form', 'mailer']);
        $rules      =   [
            'maxDateFinance'=> ['label' => 'Maximum Date for Finance Calculations ', 'rules' => 'required|valid_date[d-m-Y]'],
            'pinInput'      => ['label' => 'Secret PIN', 'rules' => 'required|numeric|exact_length[4]']
        ];

        if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());

        $viewRenderer   =   Services::renderer();
        $mainOperation  =   new MainOperation();
        $financeModel   =   new FinanceModel();
        $maxDateFinance =   $this->request->getVar('maxDateFinance');
        $maxDateFinance =   isset($maxDateFinance) && $maxDateFinance != "" ? Time::createFromFormat('d-m-Y', $maxDateFinance)->toDateString() : "";
        $maxDateFinance =   $maxDateFinance == "" ? date('Y-m-d') : $maxDateFinance;
        $pinInput       =   $this->request->getVar('pinInput');
        $notes          =   $this->request->getVar('notes');
		$idPartnerType  =	$this->userData->idPartnerType;
		$idVendor       =	$this->userData->idVendor;
		$idDriver       =	$this->userData->idDriver;
        $idPartner      =   $idPartnerType == 1 ? $idVendor : $idDriver;

        $checkPINPartner=   $mainOperation->checkPINPartner($idPartnerType, $idPartner, $pinInput);
		if(!$checkPINPartner) return throwResponseNotAcceptable("The secret PIN you entered is wrong");

		$detailPartner                  =	$mainOperation->getPartnerDetail($idPartnerType, $idPartner);
		$dataBankAccount                =	$financeModel->getDataActiveBankAccountPartner($idPartnerType, $idPartner);
		$dataRecapPerPartner            =	$financeModel->getDataRecapPerPartnerDetail($idPartnerType, $idPartner, $maxDateFinance);
		$totalUnconfirmedCollectPayment =	$financeModel->getTotalUnconfirmedCollectPayment($idPartnerType, $idPartner, $maxDateFinance);
		$totalUnfinishedSchedule        =	$financeModel->getTotalUnfinishedSchedule($idPartnerType, $idPartner, $maxDateFinance);
		$dataActiveWithdrawal           =	$financeModel->getDataActiveWithdrawal($idPartnerType, $idPartner);
        $partnerSecretPINStatus         =   $detailPartner['SECRETPINSTATUS'];
        $withdrawalBalance              =   $dataRecapPerPartner['TOTALFEE'] - $dataRecapPerPartner['TOTALCOLLECTPAYMENT'];
        $dateYesterday                  =	date('d M Y', strtotime("-1 days"));

        if($dataBankAccount == false) throwResponseNotAcceptable("Withdrawal is not allowed. Please <b>set up your bank account</b> first in the profile section");
		if($partnerSecretPINStatus != 2) throwResponseNotAcceptable("Withdrawal is not allowed. Please set your secret PIN first in the <b>User & Finance Setting</b> section");
		if($withdrawalBalance <= 0) throwResponseNotAcceptable("Withdrawal is not allowed. Your <b>balance is insufficien</b>t to withdraw");
		if($totalUnconfirmedCollectPayment > 0) throwResponseNotAcceptable("Withdrawal is not allowed. Please <b>confirm all collect payments</b> that you have");		
		if($dataActiveWithdrawal != false) throwResponseNotAcceptable("Withdrawal is not allowed. Please <b>wait for the previous withdrawal process to complete</b>");
		if($totalUnfinishedSchedule > 0) throwResponseNotAcceptable("Withdrawal is not allowed. Please <b>update all your work (until ".$dateYesterday.")</b> schedules to finish");
		
		$otpCode        =   generateRandomCharacter(4, 1);
        $partnerDetail  =   $mainOperation->getPartnerDetail($idPartnerType, $idPartner);
        $partnerEmail   =   $partnerDetail['EMAIL'];
        $partnerName    =   $partnerDetail['NAME'];

		if($partnerEmail == '-' || $partnerEmail == '') return throwResponseNotAcceptable("Unable to continue the process. The email address you have is invalid. Please contact Bali SUN Tours");

        $htmlBody       =   $viewRenderer->setVar('partnerName', $partnerName)->setVar('otpCode', $otpCode)->render('Mail/withdrawal_verification');
        sendMailHtml($partnerEmail, $partnerName, "Withdrawal Request Verification Code : ".$otpCode, $htmlBody);

        return $this->setResponseFormat('json')
                    ->respond([
                        'tokenUpdate'   =>  ['withdrawalNotes' => $notes, 'otpCodeWithdrawal' => $otpCode],
                        'message'       =>  "Please enter the OTP code that we have sent to the email address : <b>".$partnerEmail."</b>"
                    ]);	
    }

    public function submitOTPWithdrawalRequest()
    {
        helper(['form', 'mailer']);
        $rules      =   [
            'maxDateFinance'=> ['label' => 'Maximum Date for Finance Calculations ', 'rules' => 'required|valid_date[d-m-Y]'],
            'otpInput'      => ['label' => 'Verification Code', 'rules' => 'required|numeric|exact_length[4]']
        ];

        if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());

        $mainOperation  =   new MainOperation();
        $financeModel   =   new FinanceModel();
        $maxDateFinance =   $this->request->getVar('maxDateFinance');
        $maxDateFinance =   isset($maxDateFinance) && $maxDateFinance != "" ? Time::createFromFormat('d-m-Y', $maxDateFinance)->toDateString() : "";
        $maxDateFinance =   $maxDateFinance == "" ? date('Y-m-d') : $maxDateFinance;
        $otpInput       =   $this->request->getVar('otpInput');
		$idPartnerType  =	$this->userData->idPartnerType;
		$idVendor       =	$idPartnerType == 1 ? $this->userData->idVendor : 0;
		$idDriver       =	$idPartnerType == 1 ? 0 : $this->userData->idDriver;
        $idPartner      =   $idPartnerType == 1 ? $idVendor : $idDriver;
        $notes          =   $this->userData->withdrawalNotes;
        $otpCodeToken   =   $this->userData->otpCodeWithdrawal;

		if($otpInput != $otpCodeToken) return throwResponseNotAcceptable("The verification code you entered is invalid");

		$dataBankAccount    =	$financeModel->getDataActiveBankAccountPartner($idPartnerType, $idPartner);
		$dataRecapPerPartner=	$financeModel->getDataRecapPerPartnerDetail($idPartnerType, $idPartner, $maxDateFinance);

        if($dataBankAccount == false) throwResponseNotAcceptable("Withdrawal is not allowed. Please <b>set up your bank account</b> first in the profile section");

        $idBankPartner              =   $dataBankAccount['IDBANK'];
        $accountNumberPartner       =   $dataBankAccount['ACCOUNTNUMBER'];
        $accountHolderNamePartner   =   $dataBankAccount['ACCOUNTHOLDERNAME'];
        $totalFee                   =   $dataRecapPerPartner['TOTALFEE'];
        $totalCollectPayment        =   $dataRecapPerPartner['TOTALCOLLECTPAYMENT'];
        $totalWithdrawal            =   $totalFee - $totalCollectPayment;

        $arrInsertWithdrawal=	[
            "IDVENDOR"						=>	$idVendor,
            "IDDRIVER"						=>	$idDriver,
            "IDBANK"						=>	$idBankPartner,
            "TOTALFEE"						=>	$totalFee,
            "TOTALADDITIONALCOST"			=>	0,
            "TOTALCOLLECTPAYMENT"			=>	$totalCollectPayment,
            "TOTALPREPAIDCAPITAL"			=>	0,
            "TOTALLOANCARINSTALLMENT"		=>	0,
            "TOTALLOANPERSONALINSTALLMENT"	=>	0,
            "TOTALWITHDRAWAL"				=>	$totalWithdrawal,
            "MESSAGE"						=>	$notes,
            "ACCOUNTNUMBER"					=>	$accountNumberPartner,
            "ACCOUNTHOLDERNAME"				=>	$accountHolderNamePartner,
            "DATETIMEREQUEST"				=>	$this->currentDateTime
        ];
		$procInsertWithdrawal           =	$mainOperation->insertDataTable('t_withdrawalrecap', $arrInsertWithdrawal);
		
		if(!$procInsertWithdrawal['status']) switchMySQLErrorCode($procInsertWithdrawal['errCode']);
		$idWithdrawalRecap              =	$procInsertWithdrawal['insertID'];
        $arrWhereUpdateFeeWithdraw      =   [
            'IDVENDOR'          =>  $idVendor,
            'DATESCHEDULE <='   =>  $maxDateFinance,
            'WITHDRAWSTATUS'    =>  0,
            'WITHDRAWSTATUS'    =>  0,
            'IDWITHDRAWALRECAP' =>  0
        ];
        $arrWhereUpdateCollectWithdraw  =   [
            'IDVENDOR'                  =>  $idVendor,
            'IDDRIVER'                  =>  $idDriver,
            'DATECOLLECT <='            =>  $maxDateFinance,
            'STATUS'                    =>  1,
            'IDWITHDRAWALRECAP'         =>  0,
            'STATUSSETTLEMENTREQUEST'   =>  [0, -1]
        ];

        $mainOperation->updateDataTable('t_fee', ['IDWITHDRAWALRECAP'=>$idWithdrawalRecap], $arrWhereUpdateFeeWithdraw);
        $mainOperation->updateDataTable('t_collectpayment', ['IDWITHDRAWALRECAP'=>$idWithdrawalRecap], $arrWhereUpdateCollectWithdraw);
		
		if(PRODUCTION_URL){
			$partnerName			=	$this->userData->partnerName;
            $rtdbTableName          =   $idPartnerType == 1 ? 'Vendor' : 'Driver';
			$totalWithdrawalRequest	=	$financeModel->getTotalWithdrawalRequest($idPartnerType);
			$factory				=	(new Factory)->withServiceAccount(FIREBASE_PRIVATE_KEY_PATH)->withDatabaseUri(FIREBASE_RTDB_URI);
			$database				=	$factory->createDatabase();
			$reference				=	$database->getReference(FIREBASE_RTDB_WEBREF_NAME."unprocessedFinance".$rtdbTableName."/withdrawalRequest")
										->set([
											'newWithdrawalRequestStatus'	=>	true,
											'newWithdrawalRequestTotal'		=>	$totalWithdrawalRequest,
											'newWithdrawalRequestMessage'	=>	"New withdrawal request from ".$partnerName." - ".number_format($totalWithdrawal, 0, '.', ',')." IDR.<br/>Message : ".$notes,
											'timestampUpdate'				=>	gmdate("YmdHis")
										]);
            if(PRODUCTION_URL) $this->updatePartnerRTDBStatisticWithdrawal();
		}

         return $this->setResponseFormat('json')
                    ->respond([
                        'tokenUpdate'   =>  ['withdrawalNotes' => "", 'otpCodeWithdrawal' => ""],
                        'messages'      =>  ['success' => 'Your withdrawal request has been processed. Please wait for the approval process']
                    ]);	
    }

    private function updatePartnerRTDBStatisticWithdrawal(){
        $mainOperation		=   new MainOperation();
		$idPartnerType		=   $this->userData->idPartnerType;
		$idVendor           =   $this->userData->idVendor;
		$idDriver           =   $this->userData->idDriver;
		$idPartner          =   $idPartnerType == 1 ? $idVendor : $idDriver;
		$RTDB_partnerType   =   $idPartnerType == 1 ? 'vendor' : 'driver';
		$RTDB_idUserPartner =   $this->userData->RTDB_idUserPartner;
		try {
			$factory            =	(new Factory)->withServiceAccount(FIREBASE_PRIVATE_KEY_PATH)->withDatabaseUri(FIREBASE_RTDB_URI);
            $database           =	$factory->createDatabase();
            $referencePartner   =	$database->getReference(FIREBASE_RTDB_MAINREF_NAME.$RTDB_partnerType."/".$RTDB_idUserPartner."/activeWithdrawal/totalActiveWithdrawal");
            $referencePartnerGet=	$referencePartner->getValue();
            if($referencePartnerGet != null || !is_null($referencePartnerGet)){
                $referencePartner->update(
                    [
                        'newWithdrawalNotif'        =>  false,
                        'newWithdrawalNotifDetail'  =>  '',
                        'newWithdrawalNotifStatus'  =>  0,
                        'totalActiveWithdrawal'     =>  $mainOperation->getTotalActiveWithdrawal($idPartnerType, $idPartner)
                    ]
                );
            }
		} catch (\Throwable $th) {
			return true;
		}
	}
}