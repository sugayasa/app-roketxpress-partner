<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use Config\Services;
use App\Models\MainOperation;
use App\Models\FinanceModel;
use App\Models\UserFinanceSettingModel;

class UserFinanceSetting extends ResourceController
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

    public function getDetailDataUserFinance()
    {
        $mainOperation          =   new MainOperation();
        $financeModel           =   new FinanceModel();
        $userFinanceSettingModel=   new UserFinanceSettingModel();
        $idVendor               =   $this->userData->idVendor;
        $idDriver               =   $this->userData->idDriver;
        $idPartnerType          =   $this->userData->idPartnerType;
        $idPartner              =   $idPartnerType == 1 ? $idVendor : $idDriver;
		$detailPartner		    =	$mainOperation->getPartnerDetail($idPartnerType, $idPartner);
		$initialName            =	preg_match_all('/\b\w/', $detailPartner['NAME'], $matches);
		$initialName            =	implode('', $matches[0]);

		$dataBankAccount				    =	$financeModel->getDataActiveBankAccountPartner($idPartnerType, $idPartner);
		$dataListBankAccount                =	$userFinanceSettingModel->getDataListBankAccount($idPartnerType, $idPartner);
		$dataListUserPartner			    =	$userFinanceSettingModel->getDataListUserPartner($idPartnerType, $idPartner);
        $defaultSecretPIN                   =   $idPartnerType == 1 ? DEFAULT_VENDOR_PIN : DEFAULT_DRIVER_PIN;
        $partnerSecretPIN                   =   $detailPartner['SECRETPIN'];
        $partnerSecretPINStatus             =   $detailPartner['SECRETPINSTATUS'];
        $detailPartner['ISSECRETPINSET']    =   $partnerSecretPIN != $defaultSecretPIN && $partnerSecretPIN != "" && $partnerSecretPINStatus != 1 ? true : false;
        $detailPartner['INITIALNAME']       =   $initialName;
        $detailPartner['FINANCESCHEMETYPE'] =   $this->userData->financeSchemeType;
        unset($detailPartner['SECRETPIN']);

        if($dataListBankAccount){
            foreach($dataListBankAccount as $keyListBankAccount){
                $keyListBankAccount->IDBANKACCOUNTPARTNER   =   $keyListBankAccount->IDBANKACCOUNTPARTNER == 0 ? "" : hashidEncode($keyListBankAccount->IDBANKACCOUNTPARTNER);
                $keyListBankAccount->IDBANK                 =   $keyListBankAccount->IDBANK == 0 ? "" : hashidEncode($keyListBankAccount->IDBANK);
            }
        }

        if($dataListUserPartner){
            foreach($dataListUserPartner as $keyListUserPartner){
                $keyListUserPartner->ALLOWEDITING       =   $keyListUserPartner->IDUSERPARTNER == $this->userData->idUserPartner ? false : true;
                $keyListUserPartner->IDUSERPARTNER      =   $keyListUserPartner->IDUSERPARTNER == 0 ? "" : hashidEncode($keyListUserPartner->IDUSERPARTNER);
                $keyListUserPartner->IDUSERLEVELPARTNER =   $keyListUserPartner->IDUSERLEVELPARTNER == 0 ? "" : hashidEncode($keyListUserPartner->IDUSERLEVELPARTNER);
            }
        }

        return $this->setResponseFormat('json')
                    ->respond([
                        "detailPartner"         =>  $detailPartner,
                        "dataBankAccount"       =>  $dataBankAccount,
                        "dataListBankAccount"   =>  $dataListBankAccount,
                        "dataListUserPartner"   =>  $dataListUserPartner
                     ]);
    }

    public function setPartnerSecretPIN()
    {
        helper(['form', 'database']);
        $rules      =   [
            'pinInput'			=> ['label' => 'Secret PIN', 'rules' => 'required|numeric|exact_length[4]']
        ];

        if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());

        $mainOperation		=   new MainOperation();
        $pinInput		    =   $this->request->getVar('pinInput');
		$idPartnerType		=	$this->userData->idPartnerType;
		$idVendor			=	$this->userData->idVendor;
		$idDriver			=	$this->userData->idDriver;
        $idPartner          =   $idPartnerType == 1 ? $idVendor : $idDriver;
        $tableName          =   $idPartnerType == 1 ? "m_vendor" : "m_driver";
        $fieldWhere         =   $idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";

		if($pinInput == 1234 || $pinInput == DEFAULT_VENDOR_PIN){
			return throwResponseNotAcceptable("Please enter another pin");
		}

		$arrUpdatePIN       =	[
			"SECRETPIN"		        =>	$pinInput,
			"SECRETPINSTATUS"       =>	2,
			"SECRETPINLASTUPDATE"   =>	$this->currentDateTime
		];
		$procUpdatePIN      =	$mainOperation->updateDataTable($tableName, $arrUpdatePIN, [$fieldWhere=>$idPartner]);
		
		if(!$procUpdatePIN['status']) switchMySQLErrorCode($procUpdatePIN['errCode']);

        return throwResponseOK('Your secret PIN has been saved');
	}

    public function setPartnerBankAccount()
    {
        helper(['form', 'mailer']);
        $rules      =   [
            'accountBank'       => ['label' => 'Bank', 'rules' => 'required|alpha_numeric'],
            'accountNumber'     => ['label' => 'Account Number', 'rules' => 'required|numeric'],
            'accountHolderName' => ['label' => 'Account Holder Name', 'rules' => 'required|alpha_numeric_punct'],
            'pinInput'			=> ['label' => 'Secret PIN', 'rules' => 'required|numeric|exact_length[4]']
        ];

        if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());

        $viewRenderer           =   Services::renderer();
        $mainOperation		    =   new MainOperation();
        $idBank                 =   $this->request->getVar('accountBank');
        $idBank                 =   hashidDecode($idBank);
        $accountNumber          =   $this->request->getVar('accountNumber');
        $accountHolderName      =   $this->request->getVar('accountHolderName');
        $idBankAccountPartner   =   $this->request->getVar('idBankAccountPartner');
        $idBankAccountPartner   =   isset($idBankAccountPartner) && $idBankAccountPartner != '' ? hashidDecode($idBankAccountPartner) : "";
        $pinInput		        =   $this->request->getVar('pinInput');
		$idPartnerType		    =	$this->userData->idPartnerType;
		$idVendor			    =	$this->userData->idVendor;
		$idDriver			    =	$this->userData->idDriver;
        $idPartner              =   $idPartnerType == 1 ? $idVendor : $idDriver;

		if(!$idBank || ($idBankAccountPartner == false && $idBankAccountPartner != "")) return throwResponseNotAcceptable("Invalid submission data");

        $checkPINPartner        =   $mainOperation->checkPINPartner($idPartnerType, $idPartner, $pinInput);

		if(!$checkPINPartner) return throwResponseNotAcceptable("The secret PIN you entered is wrong");

		$otpCode                =   generateRandomCharacter(4, 1);
        $partnerDetail          =   $mainOperation->getPartnerDetail($idPartnerType, $idPartner);
        $partnerEmail           =   $partnerDetail['EMAIL'];
        $partnerName            =   $partnerDetail['NAME'];

		if($partnerEmail == '-' || $partnerEmail == '') return throwResponseNotAcceptable("Unable to continue the process. The email address you have is invalid. Please contact Bali SUN Tours");

        $htmlBody               =   $viewRenderer->setVar('partnerName', $partnerName)->setVar('otpCode', $otpCode)->render('Mail/bank_account_verification');
        sendMailHtml($partnerEmail, $partnerName, "Partner bank account data change Verification Code : ".$otpCode, $htmlBody);
        $arrInsertUpdBankAccount=	[
            "IDBANK"            =>  $idBank,
            "IDPARTNERTYPE"     =>  $idPartnerType,
            "IDPARTNER"         =>  $idPartner,
            "ACCOUNTNUMBER"     =>  $accountNumber,
            "ACCOUNTHOLDERNAME" =>  $accountHolderName
		];

        return $this->setResponseFormat('json')
                    ->respond([
                        'tokenUpdate'   =>  ['idBankAccountPartner' => $idBankAccountPartner, 'otpCodeBankAccount' => $otpCode, 'arrInsertUpdBankAccount' => $arrInsertUpdBankAccount],
                        'message'       =>  "Please enter the OTP code that we have sent to the email address : <b>".$partnerEmail."</b>"
                    ]);	
    }

    public function submitOTPDataBankAccount()
    {
        helper(['form', 'database']);
        $rules      =   [
            'otpInput'			=> ['label' => 'Verification Code', 'rules' => 'required|numeric|exact_length[4]']
        ];

        if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());

        $mainOperation		    =   new MainOperation();
        $financeModel           =   new FinanceModel();
        $otpInput		        =   $this->request->getVar('otpInput');
		$idBankAccountPartner   =	$this->userData->idBankAccountPartner;
		$arrInsertUpdBankAccount=	$this->userData->arrInsertUpdBankAccount;
		$otpCodeBankAccount     =	$this->userData->otpCodeBankAccount;
		$idPartnerType		    =	$this->userData->idPartnerType;
		$idVendor			    =	$this->userData->idVendor;
		$idDriver			    =	$this->userData->idDriver;
        $idPartner              =   $idPartnerType == 1 ? $idVendor : $idDriver;
		$dataBankAccount        =	$financeModel->getDataActiveBankAccountPartner($idPartnerType, $idPartner);

		if($otpInput != $otpCodeBankAccount) return throwResponseNotAcceptable("The verification code you entered is invalid");
        if(!$dataBankAccount) $arrInsertUpdBankAccount->STATUS      =   1;
        if($idBankAccountPartner == "") $procInsertUpdBankAccount   =	$mainOperation->insertDataTable('t_bankaccountpartner', $arrInsertUpdBankAccount);
        if($idBankAccountPartner != "") $procInsertUpdBankAccount   =	$mainOperation->updateDataTable('t_bankaccountpartner', $arrInsertUpdBankAccount, ['IDBANKACCOUNTPARTNER '=>$idBankAccountPartner]);
		if(!$procInsertUpdBankAccount['status']) switchMySQLErrorCode($procInsertUpdBankAccount['errCode']);

        $messageSuccess         =   $idBankAccountPartner == "" ? "New bank account data has been added" : "Bank account data has been updated";
        return $this->setResponseFormat('json')
                    ->respond([
                        'tokenUpdate'   =>  ['idBankAccountPartner' => "", 'otpCodeBankAccount' => "", 'arrInsertUpdBankAccount' => []],
                        'messages'      =>  ['success' => $messageSuccess]
                    ]);	
    }

    public function setActiveBankAccount()
    {
        helper(['form', 'mailer']);
        $rules      =   [
            'idBankAccountPartner'  => ['label' => 'Id Bank Account Partner', 'rules' => 'required|alpha_numeric']
        ];

        if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());

        $mainOperation		    =   new MainOperation();
        $idBankAccountPartner   =   $this->request->getVar('idBankAccountPartner');
        $idBankAccountPartner   =   hashidDecode($idBankAccountPartner);
		$idPartnerType		    =	$this->userData->idPartnerType;
		$idVendor			    =	$this->userData->idVendor;
		$idDriver			    =	$this->userData->idDriver;
        $idPartner              =   $idPartnerType == 1 ? $idVendor : $idDriver;

		if(!$idBankAccountPartner) return throwResponseNotAcceptable("Invalid submission data");

        $procResetBankAccount       =   $mainOperation->updateDataTable('t_bankaccountpartner', ['STATUS' => 0], ['IDPARTNERTYPE'=>$idPartnerType, 'IDPARTNER'=>$idPartner]);
        if(!$procResetBankAccount['status']) switchMySQLErrorCode($procResetBankAccount['errCode']);
        $procSetActiveBankAccount   =   $mainOperation->updateDataTable('t_bankaccountpartner', ['STATUS' => 1], ['IDBANKACCOUNTPARTNER'=>$idBankAccountPartner]);
        if(!$procSetActiveBankAccount['status']) switchMySQLErrorCode($procSetActiveBankAccount['errCode']);

        return throwResponseOK("Change of active bank account data is successful");	
    }

    public function submitDataUserPartner()
    {
        helper(['form', 'database']);
        $rules      =   [
            'nameUser'			=>  ['label' => 'Name', 'rules' => 'required|alpha_numeric|min_length[4]'],
            'username'			=>  ['label' => 'Username', 'rules' => 'required|alpha_numeric|min_length[6]'],
            'emailUser'			=>  ['label' => 'Email', 'rules' => 'required|valid_email'],
            'idUserLevelPartner'=>  ['label' => 'User Level', 'rules' => 'required|alpha_numeric'],
        ];

        if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());
        $idUserPartner          =   $this->request->getVar('idUserPartner');
        $newPassword            =   $this->request->getVar('newPassword');
        $repeatPassword         =   $this->request->getVar('repeatPassword');

        if((!isset($idUserPartner) || $idUserPartner == "" || $idUserPartner == 0) || ($newPassword != "" && $repeatPassword == "") || ($newPassword == "" && $repeatPassword != "")){
            $rules              =   [
                'newPassword'   =>  ['label' => 'Password', 'rules' => 'required|alpha_numeric|min_length[6]'],
                'repeatPassword'=>  ['label' => 'Repeat Password', 'rules' => 'required|alpha_numeric|min_length[6]|matches[newPassword]']
            ];

            if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());
        }

        $mainOperation		    =   new MainOperation();
        $userFinanceSettingModel=   new UserFinanceSettingModel();
        $nameUser		        =   $this->request->getVar('nameUser');
        $username		        =   $this->request->getVar('username');
        $userEmail		        =   $this->request->getVar('emailUser');
        $idUserLevelPartner     =   $this->request->getVar('idUserLevelPartner');
        $idUserLevelPartner     =   hashidDecode($idUserLevelPartner);
        $idUserPartner          =   isset($idUserPartner) && $idUserPartner != '' ? hashidDecode($idUserPartner) : "";
		$idPartnerType		    =	$this->userData->idPartnerType;
		$idVendor			    =	$this->userData->idVendor;
		$idDriver			    =	$this->userData->idDriver;
        $idPartner              =   $idPartnerType == 1 ? $idVendor : $idDriver;
        $messageSuccess         =   '';
        
		if(!$idUserLevelPartner) return throwResponseNotAcceptable("Invalid submission data");
        $dataUserCheck          =   $userFinanceSettingModel->checkDataUsername($idUserPartner, $username);
        $arrInsertUpdateUser    =   [
            'IDUSERLEVELPARTNER'=>  $idUserLevelPartner,
            'IDPARTNERTYPE'     =>  $idPartnerType,
            'IDVENDOR'          =>  $idVendor,
            'IDDRIVER'          =>  $idDriver,
            'NAME'              =>  $nameUser,
            'EMAIL'             =>  $userEmail,
            'USERNAME'          =>  $username
        ];

        if($idUserPartner == "" || $idUserPartner == 0){
            $arrInsertUpdateUser['PASSWORD']    =   password_hash($newPassword, PASSWORD_DEFAULT);
            $arrInsertUpdateUser['STATUS']      =   1;
            $messageSuccess =   'New user data has been added';

            if($dataUserCheck){
                if($dataUserCheck['STATUS'] == 1){
                    return throwResponseNotAcceptable("Please enter another username");
                } else {
                    $procInsertUpdate   =   $mainOperation->updateDataTable('m_userpartner', $arrInsertUpdateUser, ['IDUSERPARTNER'=>$dataUserCheck['IDUSERPARTNER']]);
                }
            } else {
                    $procInsertUpdate   =   $mainOperation->insertDataTable('m_userpartner', $arrInsertUpdateUser);
            }
        } else {
            $messageSuccess =   'User data has been updated';
            if($dataUserCheck){
                return throwResponseNotAcceptable("Please enter another username");
            } else {
                if(isset($newPassword) && $newPassword != '') $arrInsertUpdateUser['PASSWORD']    =   password_hash($newPassword, PASSWORD_DEFAULT);;
                $procInsertUpdate   =   $mainOperation->updateDataTable('m_userpartner', $arrInsertUpdateUser, ['IDUSERPARTNER'=>$idUserPartner]);
            }
        }
        if(!$procInsertUpdate['status']) switchMySQLErrorCode($procInsertUpdate['errCode']);
        
        return throwResponseOK($messageSuccess);
    }

    public function deleteUserPartner()
    {
        $rules      =   [
            'idUserPartner'  => ['label' => 'Id User Partner', 'rules' => 'required|alpha_numeric']
        ];

        if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());

        $mainOperation  =   new MainOperation();
        $idUserPartner  =   $this->request->getVar('idUserPartner');
        $idUserPartner  =   hashidDecode($idUserPartner);

		if(!$idUserPartner) return throwResponseNotAcceptable("Invalid submission data");

        $procDeleteUserPartner  =   $mainOperation->updateDataTable('m_userpartner', ['STATUS' => -2], ['IDUSERPARTNER'=>$idUserPartner]);
        if(!$procDeleteUserPartner['status']) switchMySQLErrorCode($procDeleteUserPartner['errCode']);

        return throwResponseOK("User data has been deactivated");	
    }
}