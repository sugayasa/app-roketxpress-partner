<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\I18n\Time;
use App\Models\MainOperation;
use App\Models\ScheduleVendorModel;
use App\Models\ScheduleDriverModel;
use App\Models\CollectPaymentModel;
use Kreait\Firebase\Factory;

class ScheduleReservation extends ResourceController
{
    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    use ResponseTrait;
    protected $userData, $currentDateTime, $currentDateDT;
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger) {
        parent::initController($request, $response, $logger);

        try {
            $this->userData         =   $request->userData;
            $this->currentDateTime  =   $request->currentDateTime;
            $this->currentDateDT    =   $request->currentDateDT;
        } catch (\Throwable $th) {
        }
    }

    public function index()
    {
        return $this->failForbidden('[E-AUTH-000] Forbidden Access');
    }

    public function getDataScheduleReservation()
    {
        helper(['form']);
        $rules          =   [
            'scheduleDateStart' => ['label' => 'First Date Schedule', 'rules' => 'required|exact_length[10]|valid_date[d-m-Y]']
        ];

        if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());

        $scheduleDateStart  =   $this->request->getVar('scheduleDateStart');
        $scheduleDateStartTF=   Time::createFromFormat('d-m-Y', $scheduleDateStart);
        $scheduleDateStart  =   $scheduleDateStartTF->toDateString();
        $scheduleDateEnd    =   $scheduleDateStartTF->addDays(7)->toDateString();
        $idVendor           =   $this->userData->idVendor;
        $idDriver           =   $this->userData->idDriver;
        $idPartnerType      =   $this->userData->idPartnerType;
        $idPartner          =   $idPartnerType == 1 ? $idVendor : $idDriver;
        $scheduleModel      =   $idPartnerType == 1 ? new ScheduleVendorModel() : new ScheduleDriverModel();
        $result             =   $arrDates   =   $arrDateSchedule    =   $arrDateCek =   [];
        $arrDates[]         =   [$scheduleDateStartTF->toLocalizedString('d MMM YY'), $scheduleDateStartTF->toLocalizedString('eee'), $scheduleDateStartTF->toLocalizedString('c')];
        $arrDateSchedule[]  =   $scheduleDateStartTF->toDateString();
        $dataSchedule       =	$scheduleModel->getDataScheduleReservation($idPartner, $scheduleDateStart, $scheduleDateEnd);

        for($i=1; $i<=6; $i++){
            $subsDate           =   $scheduleDateStartTF->addDays($i);
            $arrDates[]         =   [$subsDate->toLocalizedString('d MMM YY'), $subsDate->toLocalizedString('eee'), $subsDate->toLocalizedString('c')];
            $arrDateSchedule[]  =   $subsDate->toDateString();
        }

        if($dataSchedule){
            $arrOptionHour  =   json_decode(OPTION_HOUR);
            $arrHour        =   array_column($arrOptionHour, 'VALUE');
            foreach($dataSchedule as $keySchedule){
                $timeSchedule   =   $keySchedule->TIMESCHEDULE;
                $expTimeSchedule=   explode(':', $timeSchedule);
                $hourSchedule   =   $expTimeSchedule[0];
                $indexScheduleY =   array_search($hourSchedule, $arrHour);
                $indexScheduleX =   array_search($keySchedule->SCHEDULEDATE, $arrDateSchedule);
                $arrDateCek[]   =   [$keySchedule->SCHEDULEDATE, $arrDateSchedule, $hourSchedule, $arrHour];

                if($indexScheduleX !== false && $indexScheduleY !== false){
                    $classStatusProccess=   "info";
                    $expStatusProccess  =   explode(' ', $keySchedule->STATUSPROCESSNAME);
                    $statusProccessName =   $idPartnerType == 1 ? end($expStatusProccess) : $expStatusProccess[0].(isset($expStatusProccess[1]) ? " ".$expStatusProccess[1] : "");

                    switch($keySchedule->STATUSPROCESS){
                        case "0"    :   $classStatusProccess    =   "dark"; break;
                        case "1"    :   $classStatusProccess    =   $idPartnerType == 1 ? "info" : "dark"; break;
                        case "2"    :   $classStatusProccess    =   $idPartnerType == 1 ? "primary" : "info"; break;
                        case "3"    :   $classStatusProccess    =   $idPartnerType == 1 ? "success" : "primary"; break;
                        case "4"    :   $classStatusProccess    =   "success"; break;
                    }

                    $result[]               =   [
                        "indexScheduleX"    =>  $indexScheduleX,
                        "indexScheduleY"    =>  $indexScheduleY,
                        "dataSchedule"      =>  [
                            "IDSCHEDULE"            =>  hashidEncode($keySchedule->IDSCHEDULE),
                            "PRODUCTNAME"           =>  $keySchedule->PRODUCTNAME,
                            "CUSTOMERNAME"          =>  $keySchedule->CUSTOMERNAME,
                            "PAXADULT"              =>  $keySchedule->PAXADULT,
                            "PAXCHILD"              =>  $keySchedule->PAXCHILD,
                            "STATUSPROCESSNAME"     =>  $statusProccessName,
                            "STATUSPROCESSCLASS"    =>  $classStatusProccess,
                            "STATUSINCLUDECOLLECT"  =>  $keySchedule->STATUSINCLUDECOLLECT
                        ]
                    ];
                }
            }
        }

        return $this->setResponseFormat('json')
                    ->respond([
                        "arrDates"  =>  $arrDates,
                        "result"    =>  $result,
                        "arrDateCek"=>  $arrDateCek
                     ]);
    }

    public function getDetailScheduleReservation()
    {
        helper(['form']);
        $rules      =   [
            'idSchedule'    => ['label' => 'Schedule ID', 'rules' => 'required|alpha_numeric']
        ];

        $messages   =   [
            'idSchedule'    => [
                'required'=> 'Invalid submission data',
                'alpha_numeric' => 'Invalid submission data'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $idSchedule         =   $this->request->getVar('idSchedule');
        $idSchedule         =   hashidDecode($idSchedule);
        $idPartnerType      =   $this->userData->idPartnerType;
        $idVendor           =   $this->userData->idVendor;
        $idDriver           =   $this->userData->idDriver;
        $idPartner          =   $idPartnerType == 1 ? $idVendor : $idDriver;

        $mainOperation      =   new MainOperation();
        $scheduleModel      =   $idPartnerType == 1 ? new ScheduleVendorModel() : new ScheduleDriverModel();
        $collectPaymentModel=   new CollectPaymentModel();

        if(!$idSchedule) return throwResponseNotAcceptable('Invalid submission data');
        $detailData         =	$scheduleModel->getDetailScheduleReservation($idSchedule, $idPartner);
        if(!$detailData) return throwResponseNotFound('The schedule details you are looking for could not be found, please refresh data and try again');

        $idReservation          =   $detailData['IDRESERVATION'];
        $idReservationDetails   =   $detailData['IDRESERVATIONDETAILS'];
        $dateSchedule           =   $detailData['SCHEDULEDATE'];
        $statusProcess          =   $detailData['STATUSPROCESS'];

         switch($statusProcess){
            case "0"    :   $detailData['STATUSPROCESSCLASS']   =   "dark"; break;
            case "1"    :   $detailData['STATUSPROCESSCLASS']   =   $idPartnerType == 1 ? "info" : "dark"; break;
            case "2"    :   $detailData['STATUSPROCESSCLASS']   =   $idPartnerType == 1 ? "primary" : "info"; break;
            case "3"    :   $detailData['STATUSPROCESSCLASS']   =   $idPartnerType == 1 ? "success" : "primary"; break;
            case "4"    :   $detailData['STATUSPROCESSCLASS']   =   "success"; break;
        }
        
        if($detailData['STATUSINCLUDECOLLECT'] == 1){
            $detailCollectPayment   =   $collectPaymentModel->getDetailCollectPaymentSchedule($idReservation, $idPartnerType, $idVendor, $idDriver, $dateSchedule);
            if($detailCollectPayment){
                $detailData['TOTALAMOUNTIDRCOLLECTPAYMENT'] =   $detailCollectPayment['TOTALAMOUNTIDRCOLLECTPAYMENT'];
                $detailData['STATUSCOLLECTPAYMENT']         =   $detailCollectPayment['STATUSCOLLECTPAYMENT'];
                $detailData['DESCRIPTIONCOLLECTPAYMENT']    =   $detailCollectPayment['DESCRIPTIONCOLLECTPAYMENT'];
            } else {
                $detailData['STATUSINCLUDECOLLECT']         =   0;
            }
        }

        $dataNextStatusProcess              =   $mainOperation->getDataStatusProcess($idPartnerType, intval($statusProcess) + 1);
        $detailData['IDRESERVATION']        =   hashidEncode($idReservation);
        $detailData['IDRESERVATIONDETAILS'] =   hashidEncode($idReservationDetails);
        $detailData['ISFINISHED']           =   $detailData['ISFINISHED'] == 1 ? true : false;
        $detailData['IDNEXTSTATUSPROCESS']  =   hashidEncode(intval($statusProcess) + 1);
        $detailData['NEXTSTATUSPROCESSSTR'] =   $dataNextStatusProcess['STATUSPROCESSNAME'];

        unset($detailData['STATUSPROCESS']);
        return $this->setResponseFormat('json')
                    ->respond([
                        "detailData"        =>  $detailData,
                        "isDriver"          =>  $idPartnerType == 1 ? false : true,
                        "maxStatusProcess"  =>  hashidEncode($mainOperation->getMaxStatusProcess($idPartnerType))
                     ]);
    }

    public function updateScheduleTime()
    {
        helper(['form']);
        $rules      =   [
            'idSchedule'            => ['label' => 'Schedule ID', 'rules' => 'required|alpha_numeric'],
            'scheduleTimeHour'      => ['label' => 'Schedule Time (Hour)', 'rules' => 'required|numeric|exact_length[2]'],
            'scheduleTimeMinute'    => ['label' => 'Schedule Time (Minute)', 'rules' => 'required|numeric|exact_length[2]'],
        ];

        $messages   =   [
            'idSchedule'    => [
                'required'      => 'Invalid submission data',
                'alpha_numeric' => 'Invalid submission data'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $idSchedule         =   $this->request->getVar('idSchedule');
        $idSchedule         =   hashidDecode($idSchedule);
        $scheduleTimeHour   =   $this->request->getVar('scheduleTimeHour');
        $scheduleTimeMinute =   $this->request->getVar('scheduleTimeMinute');
        $scheduleTime       =   $scheduleTimeHour.":".$scheduleTimeMinute.":00";
        $idPartnerType      =   $this->userData->idPartnerType;

        if(!$idSchedule) return throwResponseNotAcceptable('Invalid submission data');
        if($idPartnerType == 2) return throwResponseNotAcceptable('This function is not available');
        
        $dataUpdateSchedule =   [
            'TIMESCHEDULE'      =>  $scheduleTime
        ];

        $scheduleModel      =   $idPartnerType == 1 ? new ScheduleVendorModel() : new ScheduleDriverModel();
        $scheduleModel->update($idSchedule, $dataUpdateSchedule);

        return throwResponseOK('Schedule time has been updated');
    }

    public function confirmCollectPayment()
    {
        helper(['form']);
        $rules      =   [
            'idReservation' => ['label' => 'Reservation ID', 'rules' => 'required|alpha_numeric'],
            'collectDate'   => ['label' => 'Collect Date', 'rules' => 'required|valid_date[Y-m-d]']
        ];

        $messages   =   [
            'idReservation'  => [
                'required'=> 'Invalid submission data',
                'alpha_numeric' => 'Invalid submission data'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $mainOperation      =   new MainOperation();
        $collectPaymentModel=   new CollectPaymentModel();
        $idReservation      =   $this->request->getVar('idReservation');
        $idReservation      =   hashidDecode($idReservation);
        $collectDate        =   $this->request->getVar('collectDate');
        $remarkNotes        =   $this->request->getVar('remarkNotes');
        $idPartnerType      =   $this->userData->idPartnerType;
        $idVendor           =   $this->userData->idVendor;
        $idDriver           =   $this->userData->idDriver;
		
		if($collectDate > $this->currentDateDT){
			return throwResponseNotAcceptable("Collect payment confirmation is not allowed before the appointed day");
		}

        $strArrIdCollectPayment	=	$collectPaymentModel->getStrArrIdCollectPaymentByDateReservation($idReservation, $idPartnerType, $idVendor, $idDriver, $collectDate);
        
		if(!$strArrIdCollectPayment){
			return throwResponseNotAcceptable("You are not allowed to perform this action");
		}

        $arrIdCollectPayment	=	explode(",", $strArrIdCollectPayment);
		foreach($arrIdCollectPayment as $idCollectPayment){
			$arrUpdateCollectPayment	=	[
                "STATUS"        =>	1,
                "DATETIMESTATUS"=>	$this->currentDateTime
            ];
			$procUpdateCollectPayment	=	$mainOperation->updateDataTable("t_collectpayment", $arrUpdateCollectPayment, ["IDCOLLECTPAYMENT" => $idCollectPayment]);

			if($procUpdateCollectPayment['status']){
				$partnerName            =	$this->userData->name;
                $partnerTypeStr         =   $idPartnerType == 1 ? "Vendor" : "Driver";
				$arrInsertCollectHistory=	[
                    "IDCOLLECTPAYMENT"	=>	$idCollectPayment,
                    "DESCRIPTION"		=>	"Partner confirms collect payment has been completed",
                    "SETTLEMENTRECEIPT"	=>	"",
                    "USERINPUT"			=>	$partnerName." (".$partnerTypeStr.")",
                    "DATETIMEINPUT"		=>	$this->currentDateTime,
                    "STATUS"			=>	1
                ];
				$mainOperation->insertDataTable("t_collectpaymenthistory", $arrInsertCollectHistory);
				
				if(isset($remarkNotes) && $remarkNotes != ""){
					$detailCollectPayment	=	$collectPaymentModel->getDetailPayment($idPartnerType, $idVendor, $idDriver, $idCollectPayment);

                    if($detailCollectPayment){
                        $idReservationPayment	=	$detailCollectPayment['IDRESERVATIONPAYMENT'];	
                        $descriptionPayment		=	$detailCollectPayment['DESCRIPTION'].". Partner Remark : ".$remarkNotes;	
                        $mainOperation->updateDataTable("t_reservationpayment", ["DESCRIPTION" => $descriptionPayment], ["IDRESERVATIONPAYMENT" => $idReservationPayment]);
                        if(PRODUCTION_URL) $this->updatePartnerRTDBStatisticCollectPayment();
                    }
				}
			}
		}

        return throwResponseOK('Collect payment has been confirmed');
    }

    public function updateStatusSchedule()
    {
        helper(['form']);
        $rules      =   [
            'idSchedule'            => ['label' => 'Schedule ID', 'rules' => 'required|alpha_numeric'],
            'idReservationDetails'  => ['label' => 'Reservation Details ID', 'rules' => 'required|alpha_numeric'],
            'statusProcess'         => ['label' => 'Status', 'rules' => 'required|alpha_numeric']
        ];

        $messages   =   [
            'idSchedule'        => [
                'required'      => 'Invalid submission data',
                'alpha_numeric' => 'Invalid submission data'
            ],
            'idReservationDetails'  => [
                'required'      => 'Invalid submission data',
                'alpha_numeric' => 'Invalid submission data'
            ],
            'statusProcess'     => [
                'required'      => 'Invalid submission data',
                'alpha_numeric' => 'Invalid submission data'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $idSchedule             =   $this->request->getVar('idSchedule');
        $idSchedule             =   hashidDecode($idSchedule);
        $idReservationDetails   =   $this->request->getVar('idReservationDetails');
        $idReservationDetails   =   hashidDecode($idReservationDetails);
        $statusProcess          =   $this->request->getVar('statusProcess');
        $statusProcess          =   hashidDecode($statusProcess);
        $idPartnerType          =   $this->userData->idPartnerType;
        $idVendor               =   $this->userData->idVendor;
        $idDriver               =   $this->userData->idDriver;
        $idPartner              =   $idPartnerType == 1 ? $idVendor : $idDriver;
        $financeSchemeType      =   $this->userData->financeSchemeType;

        $mainOperation          =   new MainOperation();
        $scheduleModel          =   $idPartnerType == 1 ? new ScheduleVendorModel() : new ScheduleDriverModel();
        $collectPaymentModel    =   new CollectPaymentModel();

        $detailSchedule         =	$scheduleModel->getDetailScheduleReservation($idSchedule, $idPartner);
		$idReservation			=	$detailSchedule['IDRESERVATION'];
		$dateScheduleDB			=	$detailSchedule['SCHEDULEDATE'];
		$scheduleDateText		=	$detailSchedule['DATEACTIVITY'];
		$customerName			=	$detailSchedule['CUSTOMERNAME'];
		$reservationTitle		=	$detailSchedule['RESERVATIONTITLE'];
		$productName			=	$detailSchedule['PRODUCTNAME'];
		$feeNominal				=	$detailSchedule['NOMINAL'];
		$feeNotes				=	$detailSchedule['NOTES'];
				
		if($dateScheduleDB > $this->currentDateDT){
			return throwResponseNotAcceptable("Order status updates are not allowed before the activity day");
		}

		$detailCollectPayment	=	$collectPaymentModel->getDetailCollectPaymentSchedule($idReservation, $idPartnerType, $idVendor, $idDriver, $dateScheduleDB);
		$maxStatusProcess		=	$mainOperation->getMaxStatusProcess($idPartnerType);
		$msg					=	"Order status updated";
		$statusReservation		=	3;

        if($statusProcess == $maxStatusProcess){
			$msg				=	"Order finished. Please tell your customer to review your service";
			$statusReservation	=	4;
			
			if($detailCollectPayment){
				$statusCollectPayment	=	$detailCollectPayment['STATUSCOLLECTPAYMENT'] * 1;
				$idCollectPayment		=	$detailCollectPayment['IDCOLLECTPAYMENT'] * 1;
				if($idCollectPayment != 0 && $statusCollectPayment != 1){
        			return throwResponseNotAcceptable("You cannot complete this order. Please confirm collect payment in this order first");
				}
			}

			if($financeSchemeType == 1){
                $arrInsertFee		=	[
                    "IDRESERVATION"			=>	$idReservation,
                    "IDRESERVATIONDETAILS"	=>	$idReservationDetails,
                    "IDVENDOR"				=>	$idVendor,
                    "IDDRIVER"				=>	$idDriver,
                    "DATESCHEDULE"			=>	$dateScheduleDB,
                    "RESERVATIONTITLE"		=>	$reservationTitle,
                    "JOBTITLE"				=>	$productName,
                    "FEENOMINAL"			=>	$feeNominal,
                    "FEENOTES"				=>	$feeNotes,
                    "DATETIMEINPUT"			=>	$this->currentDateTime
                ];
                $mainOperation->insertDataTable("t_fee", $arrInsertFee);
			} else {				
				if($detailCollectPayment){
					$idCollectPayment			=	$detailCollectPayment['IDCOLLECTPAYMENT'] * 1;
					$idReservationPayment		=	$detailCollectPayment['IDRESERVATIONPAYMENT'] * 1;
					$collectPaymentAmount		=	$detailCollectPayment['TOTALAMOUNTIDRCOLLECTPAYMENT'] * 1;
					
					if($collectPaymentAmount > 0){
						$arrInsertDepositRecord		=	[
                            "IDVENDOR"				=>	$idVendor,
                            "IDRESERVATIONDETAILS"	=>	0,
                            "IDCOLLECTPAYMENT"		=>	$idCollectPayment,
                            "DESCRIPTION"			=>	"Conversion of deposit from collect payment from customer ".$customerName." on the activity on ".$scheduleDateText.", package : ".$productName,
                            "AMOUNT"				=>	$collectPaymentAmount,
                            "USERINPUT"				=>	"Auto System",
                            "DATETIMEINPUT"			=>	$this->currentDateTime
                        ];
						$mainOperation->insertDataTable("t_depositvendorrecord", $arrInsertDepositRecord);
						
						$arrUpdateCollectPayment	=	[
                            "DATETIMESTATUS"			=>	$this->currentDateTime,
                            "STATUSSETTLEMENTREQUEST"	=>	2,
                            "LASTUSERINPUT"				=>	"Auto System"
                        ];
						$mainOperation->updateDataTable("t_collectpayment", $arrUpdateCollectPayment, ["IDCOLLECTPAYMENT" => $idCollectPayment]);
						
						$arrInsertCollectHistory	=	[
                            "IDCOLLECTPAYMENT"	=>	$idCollectPayment,
                            "DESCRIPTION"		=>	"Settlement has been approved. Fees are converted to deposit",
                            "USERINPUT"			=>	"Auto System",
                            "DATETIMEINPUT"		=>	$this->currentDateTime,
                            "STATUS"			=>	2
                        ];
						$mainOperation->insertDataTable("t_collectpaymenthistory", $arrInsertCollectHistory);
						
						$arrUpdatePayment	=	[
                            "STATUS"		=>	1,
                            "DATETIMEUPDATE"=>	$this->currentDateTime,
                            "USERUPDATE"	=>	"Auto System",
                            "EDITABLE"		=>	0,
                            "DELETABLE"		=>	0
                        ];
						$mainOperation->updateDataTable("t_reservationpayment", $arrUpdatePayment, ["IDRESERVATIONPAYMENT" => $idReservationPayment]);
					}
				}
				
				$arrInsertDepositRecord		=	[
                    "IDVENDOR"				=>	$idVendor,
                    "IDRESERVATIONDETAILS"	=>	$idReservationDetails,
                    "IDCOLLECTPAYMENT"		=>	0,
                    "DESCRIPTION"			=>	"Deposit deduction after order completion for customer ".$customerName." on the activity on ".$scheduleDateText.", package : ".$productName,
                    "AMOUNT"				=>	$feeNominal * -1,
                    "USERINPUT"				=>	"Auto System",
                    "DATETIMEINPUT"			=>	$this->currentDateTime
                ];
				$mainOperation->insertDataTable("t_depositvendorrecord", $arrInsertDepositRecord);	
			}
		}

		$tableUpdate    =	$idPartnerType == 1 ? "t_schedulevendor" : "t_scheduledriver";
		$whereUpdate    =	["IDRESERVATIONDETAILS" => $idReservationDetails];
		if($idPartnerType == 2){
			$whereUpdate["IDDRIVER"]    =	$idDriver;
		}

		$arrUpdate		=	[
            "STATUSPROCESS" =>  $statusProcess,
            "STATUS"        =>  2
        ];

		if($statusProcess == $maxStatusProcess) $arrUpdate["STATUS"]	=	3;
		$procUpdate     =	$mainOperation->updateDataTable($tableUpdate, $arrUpdate, $whereUpdate);
		
		if($procUpdate['status']){			
			$dataStatusProcess  =	$mainOperation->getDataStatusProcess($idPartnerType, $statusProcess);
            $description        =   $dataStatusProcess['STATUSPROCESSNAME'];
			$arrInsertTimeline  =	[
                "IDRESERVATIONDETAILS"  => $idReservationDetails,
                "DESCRIPTION"			=> $description,
                "DATETIME"			    => $this->currentDateTime
            ];
			$mainOperation->insertDataTable('t_reservationdetailstimeline', $arrInsertTimeline);
			$mainOperation->updateDataTable("t_reservation", ["STATUS"=>$statusReservation], ["IDRESERVATION" => $idReservation]);
			
			return throwResponseOK($msg);
		}

		return throwResponseInternalServerError("Internal server error. Please try again later");
    }

    private function updatePartnerRTDBStatisticCollectPayment(){
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
			$referencePartner   =	$database->getReference(FIREBASE_RTDB_MAINREF_NAME.$RTDB_partnerType."/".$RTDB_idUserPartner."/activeCollectPayment");
			$referencePartnerGet=	$referencePartner->getValue();
			if($referencePartnerGet != null || !is_null($referencePartnerGet)){
				$referencePartner->set([
                    'newCollectPaymentStatus'   =>  false,
                    'totalActiveCollectPayment' =>  $mainOperation->getTotalActiveCollectPayment($idPartnerType, $idPartner)
                ]);
			}
		} catch (\Throwable $th) {
			return true;
		}
	}
}