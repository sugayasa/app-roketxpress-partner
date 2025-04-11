<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\AccessModel;
use App\Models\MainOperation;
use App\Models\MessagePartnerModel;
use CodeIgniter\I18n\Time;
use Kreait\Firebase\Factory;

class Access extends ResourceController
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

    public function check()
    {
        helper(['form', 'firebaseJWT', 'hashid']);

        $rules  =   [
            'hwid'      =>  ['label' => 'Hardware ID', 'rules' => 'required|alpha_numeric_punct|min_length[10]'],
        ];

        if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());

        $hwid           =   strtoupper($this->request->getVar('hwid'));
        $header         =   $this->request->getServer('HTTP_AUTHORIZATION');
        $explodeHeader  =   $header != "" ? explode(' ', $header) : [];
        $token          =   is_array($explodeHeader) && isset($explodeHeader[1]) && $explodeHeader[1] != "" ? $explodeHeader[1] : "";
        $timeCreate     =   Time::now(APP_TIMEZONE)->toDateTimeString();
        $statusCode     =   401;
        $captchaCode    =   generateRandomCharacter(4, 3);

        $partnerData    =   array(
            "name"  =>   "",
            "email" =>   ""
        );

        $tokenPayload   =   array(
            "idUserPartner"     =>  0,
            "idUserLevelPartner"=>  0,
            "idPartnerType"     =>  0,
            "idVendor"          =>  0,
            "idDriver"          =>  0,
            "RTDB_idUserPartner"=>  "",
            "partnerName"       =>  "",
            "username"          =>  "",
            "name"              =>  "",
            "email"             =>  "",
            "captchaCode"       =>  $captchaCode,
            "transportService"  =>  false,
            "financeSchemeType" =>  1,
            "hwid"              =>  $hwid,
            "timeCreate"        =>  $timeCreate
        );

        $defaultToken           =   encodeJWTToken($tokenPayload);

        if(isset($token) && $token != ""){
            try {
                $dataDecode     =   decodeJWTToken($token);
                $idUserPartner  =   intval($dataDecode->idUserPartner);
                $hwidToken      =   $dataDecode->hwid;
                $timeCreateToken=   $dataDecode->timeCreate;

                if($idUserPartner != 0){
                    $accessModel    =   new AccessModel(); 
                    $partnerDataDB  =   $accessModel
                                        ->where("IDUSERPARTNER", $idUserPartner)
                                        ->first();

                    if(!$partnerDataDB || is_null($partnerDataDB)) return throwResponseUnauthorized('[E-AUTH-001.1.0] Not registered, Please login to continue', ['token'=>$defaultToken]);

                    $hwidDB         =   $partnerDataDB['HWID'];

                    if($hwid == $hwidDB && $hwid == $hwidToken){
                        $timeCreateToken    =   Time::parse($timeCreateToken, APP_TIMEZONE);
                        $minutesDifference  =   $timeCreateToken->difference(Time::now(APP_TIMEZONE))->getMinutes();

                        if($minutesDifference > MAX_INACTIVE_SESSION_MINUTES){
                            return throwResponseForbidden('Session expired, please log in first before perform this action');
                        }
            
                        $accessModel->update($idUserPartner, ['LASTLOGIN' => $timeCreate]);

                        $partnerDetail  =   $accessModel->getUserPartnerDetail($idUserPartner);
                        $partnerData    =   array(
                            "name"                  =>   $partnerDataDB['NAME'],
                            "email"                 =>   $partnerDataDB['EMAIL']
                        );

                        $tokenPayload['idUserPartner']      =   $idUserPartner;
                        $tokenPayload['idUserLevelPartner'] =   $partnerDataDB['IDUSERLEVELPARTNER'];
                        $tokenPayload['idPartnerType']      =   $partnerDataDB['IDPARTNERTYPE'];
                        $tokenPayload['idVendor']           =   $partnerDataDB['IDVENDOR'];
                        $tokenPayload['idDriver']           =   $partnerDataDB['IDDRIVER'];
                        $tokenPayload['RTDB_idUserPartner'] =   $partnerDetail['RTDBREFCODE'];
                        $tokenPayload['username']           =   $partnerDataDB['USERNAME'];
                        $tokenPayload['name']               =   $partnerDataDB['NAME'];
                        $tokenPayload['email']              =   $partnerDataDB['EMAIL'];
                        $tokenPayload['partnerName']        =   $partnerDetail['PARTNERNAME'];
                        $tokenPayload['transportService']   =   $partnerDetail['TRANSPORTSERVICE'] == 1 ? true : false;
                        $tokenPayload['financeSchemeType']  =   $partnerDetail['FINANCESCHEMETYPE'];
                        $statusCode                         =   200;
                    } else {
                        return throwResponseUnauthorized('[E-AUTH-001.1.2] Device ID change, please login to continue', ['token'=>$defaultToken]);
                    }
                }
            } catch (\Throwable $th) {
                return throwResponseUnauthorized('[E-AUTH-001.2.0] Invalid Token', ['token'=>$defaultToken]);
            }
        }

        $newToken       =   encodeJWTToken($tokenPayload);
        return $this->setResponseFormat('json')
                    ->respond([
                        'token'         =>  $newToken,
                        'partnerData'   =>  $partnerData
                    ])
                    ->setStatusCode($statusCode);

    }

    public function login()
    {
        helper(['form']);
        $rules  =   [
            'username'  =>  'required|min_length[5]',
            'password'  =>  'required|min_length[5]',
            'captcha'   =>  'required|alpha_numeric|exact_length[4]'
        ];

        if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());

        $accessModel    =   new AccessModel();
        $username       =   $this->request->getVar('username');
        $password       =   $this->request->getVar('password');
        $captcha        =   $this->request->getVar('captcha');
        $captchaToken   =   $this->userData->captchaCode;

        if($captcha != $captchaToken) return $this->fail('The captcha code you entered does not match');

        $dataUserPartner=   $accessModel->where("USERNAME", $username)->where("STATUS", 1)->first();

        if(!$dataUserPartner) return $this->failNotFound('No user found for the username entered');
 
        $passwordVerify =   password_verify($password, $dataUserPartner['PASSWORD']);
        if(!$passwordVerify) return $this->fail('You entered the wrong password');

        $idUserPartner      =   $dataUserPartner['IDUSERPARTNER'];
        $idUserLevelPartner =   $dataUserPartner['IDUSERLEVELPARTNER'];
        $idPartnerType      =   $dataUserPartner['IDPARTNERTYPE'];
        $idVendor           =   $dataUserPartner['IDVENDOR'];
        $idDriver           =   $dataUserPartner['IDDRIVER'];
        $idPartner          =   $idPartnerType == 1 ? $idVendor : $idDriver;
        $name               =   $dataUserPartner['NAME'];
        $email              =   $dataUserPartner['EMAIL'];

        $partnerDetail      =   $accessModel->getUserPartnerDetail($idUserPartner);
        $partnerName        =   $partnerDetail['PARTNERNAME'];
        $transportService   =   $partnerDetail['TRANSPORTSERVICE'] == 1 ? true : false;
        $financeSchemeType  =   $partnerDetail['FINANCESCHEMETYPE'];
        $RTDB_idUserPartner =   $partnerDetail['RTDBREFCODE'];
        $currentDateTime    =   $this->currentDateTime;
        $hwid               =   $this->userData->hwid;
        
        $dataUpdateUserPartner  =   [
            'HWID'          =>   $hwid,
            'LASTLOGINAT'   =>   $currentDateTime    
        ];

        $accessModel        =   new AccessModel();
        $accessModel->where('HWID', $hwid)->set('HWID', 'null', false)->update();
        $accessModel->update($idUserPartner, $dataUpdateUserPartner);

        if(PRODUCTION_URL){
            $RTDB_partnerType   =   $idPartnerType == 1 ? 'vendor' : 'driver';
			try {
                $mainOperation      =   new MainOperation();
                $factory            =	(new Factory)->withServiceAccount(FIREBASE_PRIVATE_KEY_PATH)->withDatabaseUri(FIREBASE_RTDB_URI);
                $database           =	$factory->createDatabase();
                $referencePartner   =	$database->getReference(FIREBASE_RTDB_MAINREF_NAME.$RTDB_partnerType."/".$RTDB_idUserPartner)->getValue();
                $arrReferenceData   =   [
                    'unconfirmedReservation'=>  [
                        'newReservationStatus'          =>  false,
                        'timestampUpdate'               =>  gmdate('YmdHis'),
                        'newReservationDateTime'        =>  '',
                        'newReservationJobTitle'        =>  '',
                        'totalUnconfirmedReservation'   =>  $mainOperation->getTotalUnconfirmedReservation($idPartnerType, $idPartner),
                        'cancelReservationStatus'       =>  false,
                        'cancelReservationDetails'      =>  ''
                    ],
                    'activeCollectPayment'  =>  [
                        'newCollectPaymentStatus'   =>  false,
                        'timestampUpdate'           =>  gmdate('YmdHis'),
                        'newCollectPaymentDetail'   =>  '',
                        'totalActiveCollectPayment' =>  $mainOperation->getTotalActiveCollectPayment($idPartnerType, $idPartner)
                    ],
                    'activeWithdrawal'      =>  [
                        'newWithdrawalNotif'        =>  false,
                        'newWithdrawalNotifDetail'  =>  '',
                        'newWithdrawalNotifStatus'  =>  0,
                        'timestampUpdate'           =>  gmdate('YmdHis'),
                        'totalActiveWithdrawal'     =>  $mainOperation->getTotalActiveWithdrawal($idPartnerType, $idPartner)
                    ]
                ];
                
                if($RTDB_idUserPartner == '' || $referencePartner == null || is_null($referencePartner)){
                    $pushReference  =   $database->getReference(FIREBASE_RTDB_MAINREF_NAME.$RTDB_partnerType)->push($arrReferenceData);
                    $pushKey        =   $pushReference->getKey();
                    $tableName      =   $idPartnerType == 1 ? "m_vendor" : "m_driver";
                    $fieldWhere     =   $idPartnerType == 1 ? "IDVENDOR" : "IDDRIVER";
                    $mainOperation->updateDataTable($tableName, ['RTDBREFCODE'=>$pushKey], [$fieldWhere=>$idPartner]);            
                } else {
                    $database->getReference(FIREBASE_RTDB_MAINREF_NAME.$RTDB_partnerType."/".$RTDB_idUserPartner)->update($arrReferenceData);
                }
			} catch (\Throwable $th) {
			}
        }

        $tokenUpdate        =   array(
            "idUserPartner"         =>  $idUserPartner,
            "idUserLevelPartner"    =>  $idUserLevelPartner,
            "idPartnerType"         =>  $idPartnerType,
            "idVendor"              =>  $idVendor,
            "idDriver"              =>  $idDriver,
            "RTDB_idUserPartner"    =>  $RTDB_idUserPartner,
            "partnerName"           =>  $partnerName,
            "username"              =>  $username,
            "name"                  =>  $name,
            "email"                 =>  $email,
            "transportService"      =>  $transportService,
            "financeSchemeType"     =>  $financeSchemeType
        );
        
        return $this->setResponseFormat('json')
                    ->respond([
                        'tokenUpdate'   =>  $tokenUpdate,
                        'message'       =>  "Login successfully"
                    ]);		
    }

    public function logout($token = false)
    {
        if(!$token || $token == "") return $this->failUnauthorized('[E-AUTH-001.1] Token Required');
        helper(['firebaseJWT']);

        try {
            $dataDecode         =   decodeJWTToken($token);
            $idUserPartner      =   $dataDecode->idUserPartner;
            $hwid               =   $dataDecode->hwid;
            $accessModel        =   new AccessModel();
            $userPartnerDataDB  =   $accessModel
                                    ->where("IDUSERPARTNER", $idUserPartner)
                                    ->first();

            if(!$userPartnerDataDB || is_null($userPartnerDataDB)) return $this->failUnauthorized('[E-AUTH-001.3] Invalid Token - Not Registered');

            $hwidDB             =   $userPartnerDataDB['HWID'];

            if($hwid == $hwidDB){
                $accessModel->where('HWID', $hwid)->set('HWID', 'null', false)->update();
            }

            return redirect()->to(BASE_URL.'logoutPage');
        } catch (\Throwable $th) {
            return $this->failUnauthorized('[E-AUTH-001.2] Invalid Token'.$th->getMessage());
        }
    }

    public function captcha($token = '')
    {
        if(!$token || $token == "") $this->returnBlankCaptcha();
        helper(['firebaseJWT']);

        try {
            $dataDecode     =   decodeJWTToken($token);
            $captchaCode    =   $dataDecode->captchaCode;
            $codeLength     =   strlen($captchaCode);

            generateCaptchaImage($captchaCode, $codeLength);
        } catch (\Throwable $th) {
            $this->returnBlankCaptcha();
        }
    }

    private function returnBlankCaptcha()
    {
        $img    =   imagecreatetruecolor(120, 20);
        $bg     =   imagecolorallocate ( $img, 255, 255, 255 );
        imagefilledrectangle($img, 0, 0, 120, 20, $bg);
        
        ob_start();
        imagejpeg($img, "blank.jpg", 100);
        $contents = ob_get_contents();
        ob_end_clean();

        $dataUri = "data:image/jpeg;base64," . base64_encode($contents);
        echo $dataUri;
    }

    public function getDataOption()
    {
        $accessModel            =   new AccessModel();
        $idPartnerType          =   $this->userData->idPartnerType;
        $dataMessagePartnerType =   $accessModel->getDataMessagePartnerType($idPartnerType);
        $dataBank               =   $accessModel->getDataBank();
        $dataUserLevel          =   $accessModel->getDataUserLevel();
        $dataUserLevelMenu      =   $accessModel->getDataUserLevelMenu();
        $arrUserLevelMenu       =   [];
		
		if($dataMessagePartnerType){		
			foreach($dataMessagePartnerType as $keyMessagePartnerType){
                $keyMessagePartnerType->ID  =   hashidEncode($keyMessagePartnerType->ID);
			}		
		}

        if($dataBank){		
			foreach($dataBank as $keyBank){
                $keyBank->ID    =   hashidEncode($keyBank->ID);
			}		
		}

        if($dataUserLevel){		
			foreach($dataUserLevel as $keyUserLevel){
                $keyUserLevel->ID   =   hashidEncode($keyUserLevel->ID);
			}		
		}

        if(!is_null($dataUserLevelMenu)){
            foreach($dataUserLevelMenu as $keyUserLevelMenu){
                $arrUserLevelMenu[hashidEncode($keyUserLevelMenu->ID)][]    =   $keyUserLevelMenu->VALUE;
            }
        }

        return $this->setResponseFormat('json')
                    ->respond([
                        "data"  =>  [
                            "dataMessagePartnerType"=> $dataMessagePartnerType,
                            "dataBank"				=> $dataBank,
                            "dataUserLevel"         => $dataUserLevel,
                            "arrUserLevelMenu"      => $arrUserLevelMenu
                        ]
                    ]);
    }
	
    public function unreadNotificationList()
    {
        $messagePartnerModel    =   new MessagePartnerModel();
        $idPartnerType          =   $this->userData->idPartnerType;
        $idVendor               =   $this->userData->idVendor;
        $idDriver               =   $this->userData->idDriver;
        $unreadNotificationList	=	$messagePartnerModel->getUnreadNotificationList($idPartnerType, $idVendor, $idDriver);
		$totalUnreadNotification=	0;
		$unreadNotificationArray=	array();
		
		if($unreadNotificationList){		
			foreach($unreadNotificationList as $unreadNotificationData){
				if(count($unreadNotificationArray) < 10){
					$unreadNotificationArray[]	=	$unreadNotificationData;
				}
                $unreadNotificationData->IDMESSAGEPARTNER   =   hashidEncode($unreadNotificationData->IDMESSAGEPARTNER);
                $unreadNotificationData->IDPRIMARY          =   hashidEncode($unreadNotificationData->IDPRIMARY);
				$totalUnreadNotification++;
			}		
		}

        return $this->setResponseFormat('json')
                    ->respond([
                        "totalUnreadNotification"   =>  $totalUnreadNotification,
                        "unreadNotificationArray"   =>  $unreadNotificationArray
                     ]);
    }

    public function dismissNotification()
    {
        helper(['form']);
        $rules      =   [
            'idMessagePartner'  => ['label' => 'Message Partner ID', 'rules' => 'required|alpha_numeric']
        ];

        $messages   =   [
            'idMessagePartner'  => [
                'required'=> 'Invalid submission data',
                'alpha_numeric' => 'Invalid submission data'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $messagePartnerModel    =   new MessagePartnerModel();
        $idMessagePartner       =   $this->request->getVar('idMessagePartner');
        $idMessagePartner       =   hashidDecode($idMessagePartner);

        $messagePartnerModel->where('IDMESSAGEPARTNER', $idMessagePartner)->set('DATETIMEREAD', $this->currentDateTime)->update();

        return $this->setResponseFormat('json')
                    ->respond([
                        "message"   =>  "Notification message deleted"
                     ]);
    }

    public function dismissAllNotification()
    {
        $messagePartnerModel    =   new MessagePartnerModel();
        $idPartnerType          =   $this->userData->idPartnerType;
        $idVendor               =   $this->userData->idVendor;
        $idDriver               =   $this->userData->idDriver;
        $valueWhere             =   $idPartnerType == 1 ? $idVendor : $idDriver;

        $messagePartnerModel
        ->where('IDPARTNERTYPE', $idPartnerType)
        ->where('IDPARTNER', $valueWhere)
        ->set('DATETIMEREAD', $this->currentDateTime)
        ->update();

        return $this->setResponseFormat('json')
                    ->respond([
                        "message"   =>  "All notification message have been deleted"
                     ]);
    }

    public function detailProfileSetting()
    {
        $username   =   $this->userData->username;
        $name       =   $this->userData->name;
        $email      =   $this->userData->email;

        return $this->setResponseFormat('json')
                    ->respond([
                        "username"  =>  $username,
                        "name"      =>  $name,
                        "email"     =>  $email
                     ]);
    }

    public function getDataDashboard()
    {
        helper(['form']);
        $rules  =   [
            'month' =>  'required|exact_length[2]|numeric',
            'year'  =>  'required|exact_length[4]|numeric'
        ];

        if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());

        $accessModel        =   new AccessModel();
        $idPartnerType      =   $this->userData->idPartnerType;
        $idVendor           =   $this->userData->idVendor;
        $idDriver           =   $this->userData->idDriver;
        $month              =   $this->request->getVar('month');
        $year               =   $this->request->getVar('year');
		$yearMonth			=	$year."-".$month;
		$firstDateYearMonth	=	$year."-".$month."-01";
		$lastDateYearMonth	=	date('Y-m-t', strtotime($firstDateYearMonth));
		$lastYearMonth		=	date("Y-m", strtotime('-1 month', strtotime($firstDateYearMonth)));
		$minReservationDate	=	"2022-04-01";
		$dataReservation	=	$idPartnerType == 1 ?
                                $accessModel->getDataTotalReservationVendor($yearMonth, $lastYearMonth, $idVendor) :
                                $accessModel->getDataTotalReservationDriver($yearMonth, $lastYearMonth, $idDriver);
		
		if($dataReservation){
			$dataReservation['PERCENTAGETHISMONTH']		=	0;
			$dataReservation['PERCENTAGETHISMONTHSTYLE']=	0;
			$dataReservation['PERCENTAGETODAY']			=	0;
			$dataReservation['PERCENTAGETOMORROW']		=	0;
			$totalReservationThisMonth					=	$dataReservation['TOTALRESERVATIONTHISMONTH'];
		}
		
		if($totalReservationThisMonth > 0){
			$dataReservation['PERCENTAGETHISMONTH']		=	$dataReservation['TOTALRESERVATIONLASTMONTH'] == 0 ? 0 : number_format($totalReservationThisMonth / $dataReservation['TOTALRESERVATIONLASTMONTH'] * 100, 0, '.', ',');
			$dataReservation['PERCENTAGETHISMONTHSTYLE']=	$dataReservation['PERCENTAGETHISMONTH'] > 100 ? 100 : $dataReservation['PERCENTAGETHISMONTH'];
			$dataReservation['PERCENTAGETODAY']			=	$totalReservationThisMonth == 0 ? 0 : number_format($dataReservation['TOTALRESERVATIONTODAY'] / $totalReservationThisMonth * 100, 0, '.', ',');
			$dataReservation['PERCENTAGETOMORROW']		=	$totalReservationThisMonth == 0 ? 0 : number_format($dataReservation['TOTALRESERVATIONTOMORROW'] / $totalReservationThisMonth * 100, 0, '.', ',');
			$minReservationDate							=	$dataReservation['MINRESERVATIONDATE'];
		}

		$year1				=	date('Y', strtotime($minReservationDate));
		$year2				=	date('Y', strtotime($firstDateYearMonth));
		$month1				=	date('m', strtotime($minReservationDate));
		$month2				=	date('m', strtotime($firstDateYearMonth));
		$totalMonth			=	(($year2 - $year1) * 12) + ($month2 - $month1);
		$dataTopProduct     =	$idPartnerType == 1 ?
                                $accessModel->getDataTopProductVendor($yearMonth, $totalMonth, $lastDateYearMonth, $idVendor) :
                                $accessModel->getDataTopProductDriver($yearMonth, $totalMonth, $lastDateYearMonth, $idDriver);
		$dataStatistic		=	$this->getDataStatistic($yearMonth, $firstDateYearMonth, $idPartnerType, $idVendor, $idDriver);
		
		return $this->setResponseFormat('json')
                    ->respond([
                        "lastYearMonth"		=>	$lastYearMonth,
                        "dataReservation"	=>	$dataReservation,
                        "dataTopProduct"    =>	$dataTopProduct,
                        "dataStatistic"		=>	$dataStatistic,
                        "minReservationDate"=>	$minReservationDate,
                        "totalMonth"		=>	$totalMonth
                    ]);
	}
	
	private function getDataStatistic($yearMonth, $firstDate, $idPartnerType, $idVendor, $idDriver)
    {	
        $accessModel            =   new AccessModel();
		$totalDays			    =	date("t", strtotime($firstDate));
		$dataGraphReservation	=	$idPartnerType == 1 ?
                                    $accessModel->getDataGraphReservationVendor($yearMonth, $idVendor) :
                                    $accessModel->getDataGraphReservationDriver($yearMonth, $idDriver);
		$arrDates			    =	$arrDetailData	=	$arrDatesCheck   =   $arrTotalReservationDate   =   array();
		
		for($i=0; $i<$totalDays; $i++){
			$dateCheck		    =	date('Y-m-d', strtotime('+'.$i.' day', strtotime($firstDate)));
			$dateStr		    =	date('d', strtotime('+'.$i.' day', strtotime($firstDate)));
			
			$arrDates[]		    =	$dateStr;
			$arrDatesCheck[]    =	$dateCheck;
            $arrTotalReservationDate[]	=	0;			
		}
		
		if($dataGraphReservation){
			foreach($dataGraphReservation as $keyGraphReservation){
				$dateCheckDB=	$keyGraphReservation->SCHEDULEDATE;
				$index		=	array_search($dateCheckDB, $arrDatesCheck);
				
				$arrTotalReservationDate[$index]	=	$keyGraphReservation->TOTALRESERVATION;
			}
		}
		
        $arrDetailData[]	=	array(
                                        "label"			=>	"Total Reservartion",
                                        "data"			=>	$arrTotalReservationDate,
                                        "borderColor"	=>	"#4dc9f6",
                                        "borderWidth"	=>	3,
                                        "fill"			=>	false,
                                        "lineTension"	=>	0.3
                                    );
		
		return array(
						"arrDates"		=>	$arrDates,
						"arrDetailData"	=>	$arrDetailData
        );	
	}

    public function saveDetailProfileSetting()
    {
        helper(['form']);
        $idUserPartner  =   $this->userData->idUserPartner;
        $rules          =   [
            'username'  => ['label' => 'Username', 'rules' => 'required|alpha_numeric|min_length[4]'],
            'name'      => ['label' => 'Name', 'rules' => 'required|alpha_numeric_space|min_length[4]'],
            'email'     => ['label' => 'Email', 'rules' => 'required|valid_email|is_unique[m_userpartner.EMAIL, IDUSERPARTNER, '.$idUserPartner.']']
        ];

        $messages   =   [
            'email' => ['is_unique' => 'This email address has been previously registered'],
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $accessModel            =   new AccessModel();
        $username               =   $this->request->getVar('username');
        $name                   =   $this->request->getVar('name');
        $email                  =   $this->request->getVar('email');
        $oldPassword            =   $this->request->getVar('oldPassword');
        $newPassword            =   $this->request->getVar('newPassword');
        $repeatPassword         =   $this->request->getVar('repeatPassword');
        $relogin                =   false;

        $arrUpdateUserPartner   =   [
            'NAME'      =>  $name,
            'EMAIL'     =>  $email,
            'USERNAME'  =>  $username
        ];

        if($oldPassword != "" || $newPassword != "" || $repeatPassword != ""){
			if($oldPassword == "") return throwResponseNotAcceptable("Please enter the old password (your active password)");

			if($newPassword == "") return throwResponseNotAcceptable("Please enter a new password");

            if($repeatPassword == "") return throwResponseNotAcceptable("Please enter a new password reset");
			
			if($newPassword != $repeatPassword) return throwResponseNotAcceptable("The repetition of the new password does not match");
			
            $dataUserPartner    =   $accessModel->where("IDUSERPARTNER", $idUserPartner)->first();
            if(!$dataUserPartner) return $this->failNotFound('Your user data is not found, please try again later');
            $passwordVerify     =   password_verify($oldPassword, $dataUserPartner['PASSWORD']);
            if(!$passwordVerify) return $this->fail('The old password you entered is incorrect');
			
			$arrUpdateUserPartner['PASSWORD']	=	password_hash($newPassword, PASSWORD_DEFAULT);
            $relogin    =   true;
		}

        $accessModel->update($idUserPartner, $arrUpdateUserPartner);
        $tokenUpdate            =   [
            "username"  =>  $username,
            "name"      =>  $name,
            "email"     =>  $email
        ];

        return $this->setResponseFormat('json')
                    ->respond([
                        "message"       =>  "User data has been updated",
                        "name"          =>  $name,
                        "email"         =>  $email,
                        "relogin"       =>  $relogin,
                        "tokenUpdate"   =>  $tokenUpdate
                     ]);
    }
}