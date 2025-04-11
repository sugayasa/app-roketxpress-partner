<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\I18n\Time;
use App\Models\MainOperation;
use App\Models\ReservationModel;
use App\Models\ScheduleVendorModel;
use App\Models\ScheduleDriverModel;
use Kreait\Firebase\Factory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;

class Reservation extends ResourceController
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

    public function getDataReservation()
    {
        helper(['form']);
        $rules          =   [
            'confirmation'  => ['label' => 'Confirmation Status', 'rules' => 'is_bool'],
            'page'          => ['label' => 'Page', 'rules' => 'required|numeric|min_length[1]'],
            'orderBy'       => ['label' => 'Order By', 'rules' => 'required|numeric|min_length[1]'],
            'orderType'     => ['label' => 'Order Type', 'rules' => 'required|in_list[ASC,DESC]']
        ];

        if(!$this->validate($rules)) return $this->fail($this->validator->getErrors());

        $reservationModel   =   new ReservationModel();
        $confirmation       =   $this->request->getVar('confirmation');
        $page               =   $this->request->getVar('page');
        $orderBy            =   $this->request->getVar('orderBy');
        $orderType          =   $this->request->getVar('orderType');
        $reservationStatus  =   $this->request->getVar('reservationStatus');
        $searchKeyword      =   $this->request->getVar('searchKeyword');
        $startActivityDate  =   $this->request->getVar('startActivityDate');
        $startActivityDate  =   isset($startActivityDate) && $startActivityDate != "" ? Time::createFromFormat('d-m-Y', $startActivityDate)->toDateString() : "";
        $endActivityDate    =   $this->request->getVar('endActivityDate');
        $endActivityDate    =   isset($endActivityDate) && $endActivityDate != "" ? Time::createFromFormat('d-m-Y', $endActivityDate)->toDateString() : "";
        $bookingCode        =   $this->request->getVar('bookingCode');
        $customerName       =   $this->request->getVar('customerName');
        $locationName       =   $this->request->getVar('locationName');
        $idVendor           =   $this->userData->idVendor;
        $idDriver           =   $this->userData->idDriver;
        $idPartnerType      =   $this->userData->idPartnerType;
        $idPartner          =   $idPartnerType == 1 ? $idVendor : $idDriver;
        $transportService   =   $this->userData->transportService;
        $orderByStr         =   $urlExcelData   =   "";

        switch($orderBy){
            case 3  :   $orderByStr =   "A.DATETIMECONFIRM"; break;
            case 2  :   $orderByStr =   "A.DATETIMEINPUT"; break;
            case 1  :   
            default :   $orderByStr =   "B.SCHEDULEDATE"; break;
        }
		
		if($startActivityDate == "" && $endActivityDate != ""){
			$startActivityDate  =   $endActivityDate;
		}
		
		if($startActivityDate != "" && $endActivityDate == ""){
			$endActivityDate    =	$startActivityDate;
		}

        $result     =	$reservationModel->getDataReservation($idPartnerType, $idPartner, $confirmation, $page, $orderByStr, $orderType, $reservationStatus, $startActivityDate, $endActivityDate, $bookingCode, $customerName, $locationName, $transportService, $searchKeyword);

        if(count($result['data']) > 0){
            foreach($result['data'] as $keyData){
                if($idPartnerType == 1) $keyData->IDSCHEDULEVENDOR  =   hashidEncode($keyData->IDSCHEDULEVENDOR);
                if($idPartnerType == 2) $keyData->IDSCHEDULEDRIVER  =   hashidEncode($keyData->IDSCHEDULEDRIVER);
            }
            if($confirmation){
                $arrParamExcelDetail    =   [
                    'orderByStr'        =>  $orderByStr,
                    'orderType'         =>  $orderType,
                    'reservationStatus' =>  $reservationStatus,
                    'searchKeyword'     =>  $searchKeyword,
                    'startActivityDate' =>  $startActivityDate,
                    'endActivityDate'   =>  $endActivityDate,
                    'bookingCode'       =>  $bookingCode,
                    'customerName'      =>  $customerName,
                    'locationName'      =>  $locationName,
                    'transportService'  =>  $transportService,
                    'idPartnerType'		=>  $idPartnerType,
                    'idPartner'			=>  $idPartner
                ];
                $urlExcelData           =   BASE_URL."reservation/excelDetailReservation/".encodeJWTToken($arrParamExcelDetail);
            }
        }

        return $this->setResponseFormat('json')
                    ->respond([
                        "result"        =>  $result,
                        "isDriver"      =>  $idPartnerType == 1 ? false : true,
                        "urlExcelData"  =>  $urlExcelData
                     ]);
    }
    
    public function excelDetailReservation($encryptedParam)
    {
        helper(['firebaseJWT']);

        $mainOperation          =   new MainOperation();
        $reservationModel       =   new ReservationModel();
		$arrParam               =	decodeJWTToken($encryptedParam);
		$idPartnerType		    =	$arrParam->idPartnerType;
		$idPartner			    =	$arrParam->idPartner;
		$confirmation           =	true;
		$orderByStr             =	$arrParam->orderByStr;
		$orderType              =	$arrParam->orderType;
		$reservationStatus      =	$arrParam->reservationStatus;
		$startActivityDate      =	$arrParam->startActivityDate;
		$endActivityDate        =	$arrParam->endActivityDate;
		$bookingCode            =	$arrParam->bookingCode;
		$customerName           =	$arrParam->customerName;
		$locationName           =	$arrParam->locationName;
		$transportService       =	$arrParam->transportService;
		$searchKeyword          =	$arrParam->searchKeyword;
		$detailsPartner		    =	$idPartnerType == 1 ? $mainOperation->getVendorDetailsById($idPartner) : $mainOperation->getDriverDetailsById($idPartner);
		$detailsPartner		    =	isset($idPartner) && $idPartner != "" && $idPartner != 0 ? $detailsPartner : ['NAME' => '-'];
        $partnerName		    =   $detailsPartner['NAME'];
        $result                 =	$reservationModel->getDataReservation($idPartnerType, $idPartner, $confirmation, 1, $orderByStr, $orderType, $reservationStatus, $startActivityDate, $endActivityDate, $bookingCode, $customerName, $locationName, $transportService, $searchKeyword);
        $reservationStatusStr   =   'All Status';

        switch($reservationStatus){
            case "1"    :   $reservationStatusStr   =   'Scheduled'; break;
            case "2"    :   $reservationStatusStr   =   'On Process'; break;
            case "3"    :   $reservationStatusStr   =   'Done'; break;
            default     :   $reservationStatusStr   =   'All Status'; break;
        }
		
		if(count($result['data']) <= 0){
            return throwResponseNotFound("No data found for this action");
		}
		
		$spreadsheet	=	new Spreadsheet();
		$sheet			=	$spreadsheet->getActiveSheet();
		
		$sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
		$sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
		$sheet->getPageMargins()->setTop(0.25)->setRight(0.2)->setLeft(0.2)->setBottom(0.25);
		
		$sheet->setCellValue('A1', 'Bali Sun Tours');
		$sheet->setCellValue('A2', 'Detail Reservation Report');
		$sheet->getStyle('A1:A2')->getFont()->setBold( true );
		$sheet->getStyle('A1:A2')->getAlignment()->setHorizontal('center');
		$sheet->mergeCells('A1:P1')->mergeCells('A2:P2');
		
		$sheet->setCellValue('A4', 'Partner : '.$partnerName);							    $sheet->mergeCells('A4:P4');
		$sheet->setCellValue('A5', 'Period : '.$startActivityDate.' - '.$endActivityDate);  $sheet->mergeCells('A5:P5');
		$sheet->setCellValue('A6', 'Reservation Status : '.$reservationStatusStr);          $sheet->mergeCells('A6:P6');
		$sheet->setCellValue('A7', 'Booking Code (Contains) : '.$bookingCode);              $sheet->mergeCells('A7:P7');
		$sheet->setCellValue('A8', 'Customer Name (Contains) : '.$customerName);            $sheet->mergeCells('A8:P8');
		$sheet->setCellValue('A9', 'Search Keyword : '.$searchKeyword);                     $sheet->mergeCells('A9:P9');
				
		$sheet->setCellValue('A11', 'Date Time');           $sheet->mergeCells('A11:D11');
		$sheet->setCellValue('E11', 'Booking Details');     $sheet->mergeCells('E11:G11');
		$sheet->setCellValue('H11', 'Customer Details');    $sheet->mergeCells('H11:H12');
		$sheet->setCellValue('I11', 'Remark');              $sheet->mergeCells('I11:I12');
		$sheet->setCellValue('J11', 'Location');            $sheet->mergeCells('J11:K11');
		$sheet->setCellValue('L11', 'Pax Type');            $sheet->mergeCells('L11:L12');
		$sheet->setCellValue('M11', 'Pax Number');          $sheet->mergeCells('M11:M12');
		$sheet->setCellValue('N11', 'Price/Pax');           $sheet->mergeCells('N11:N12');
		$sheet->setCellValue('O11', 'Total Price');         $sheet->mergeCells('O11:O12');
		$sheet->setCellValue('P11', 'Grand Total');         $sheet->mergeCells('P11:P12');
		
		$sheet->setCellValue('A12', 'Schedule');
		$sheet->setCellValue('B12', 'Requested Slot');
		$sheet->setCellValue('C12', 'Reception');
		$sheet->setCellValue('D12', 'Confirmation');
		$sheet->setCellValue('E12', 'Source - Booking Code');
		$sheet->setCellValue('F12', 'Reservation Title');
		$sheet->setCellValue('G12', 'Package');
		$sheet->setCellValue('J12', 'Zone');
		$sheet->setCellValue('K12', 'Hotel / Pick Up Location');
		
		$sheet->getStyle('A11:P12')->getFont()->setBold( true );
		$sheet->getStyle('A11:P12')->getAlignment()->setHorizontal('center');
		$sheet->getStyle('A11:P12')->getAlignment()->setVertical('center');
		$rowNumber			=	$firstRowNumber	=	13;
		$grandTotalPrice	=	$grandTotalPax  =   0;
		
		$styleArray = [
			'borders' => [
				'allBorders' => [
					'borderStyle' => Border::BORDER_THIN
				]
			 ]
		];
		
		foreach($result['data'] as $data){
			
            $totalReservationPax    =   $data->PAXADULT + $data->PAXCHILD;
            $totalReservationPrice  =   $data->PRICETOTALADULT + $data->PRICETOTALCHILD;
			$sheet->setCellValue('A'.$rowNumber, $data->DATEACTIVITY." - ".$data->TIMESCHEDULE);    $sheet->mergeCells('A'.$rowNumber.':A'.($rowNumber + 1));
			$sheet->setCellValue('B'.$rowNumber, $data->TIMEBOOKING);                               $sheet->mergeCells('B'.$rowNumber.':B'.($rowNumber + 1));
			$sheet->setCellValue('C'.$rowNumber, $data->DATETIMERECEPTION);                         $sheet->mergeCells('C'.$rowNumber.':C'.($rowNumber + 1));
			$sheet->setCellValue('D'.$rowNumber, $data->DATETIMECONFIRM);                           $sheet->mergeCells('D'.$rowNumber.':D'.($rowNumber + 1));
			$sheet->setCellValue('E'.$rowNumber, "[".$data->SOURCENAME."] - ".$data->BOOKINGCODE);  $sheet->mergeCells('E'.$rowNumber.':E'.($rowNumber + 1));
			$sheet->setCellValue('F'.$rowNumber, $data->RESERVATIONTITLE);                          $sheet->mergeCells('F'.$rowNumber.':F'.($rowNumber + 1));
			$sheet->setCellValue('G'.$rowNumber, $data->PRODUCTNAME);                               $sheet->mergeCells('G'.$rowNumber.':G'.($rowNumber + 1));
			$sheet->setCellValue('H'.$rowNumber, $data->CUSTOMERNAME."\nContact : ".
                                                 $data->CUSTOMERCONTACT."\nEmail : ".
                                                 $data->CUSTOMEREMAIL);                             $sheet->mergeCells('H'.$rowNumber.':H'.($rowNumber + 1));
			$sheet->setCellValue('I'.$rowNumber, $data->REMARK);                                    $sheet->mergeCells('I'.$rowNumber.':I'.($rowNumber + 1));
			$sheet->setCellValue('J'.$rowNumber, $data->AREANAME);                                  $sheet->mergeCells('J'.$rowNumber.':J'.($rowNumber + 1));
			$sheet->setCellValue('K'.$rowNumber, $data->HOTELNAME." - ".$data->PICKUPLOCATION);     $sheet->mergeCells('K'.$rowNumber.':K'.($rowNumber + 1));
			$sheet->setCellValue('L'.$rowNumber, 'Adult');
			$sheet->setCellValue('L'.($rowNumber + 1), 'Child');
			$sheet->setCellValue('M'.$rowNumber, $data->PAXADULT);
			$sheet->setCellValue('M'.($rowNumber + 1), $data->PAXCHILD);
			$sheet->setCellValue('N'.$rowNumber, $data->PRICEPERPAXADULT);
			$sheet->setCellValue('N'.($rowNumber + 1), $data->PRICEPERPAXCHILD);
			$sheet->setCellValue('O'.$rowNumber, $data->PRICETOTALADULT);
			$sheet->setCellValue('O'.($rowNumber + 1), $data->PRICETOTALCHILD);
			$sheet->setCellValue('P'.$rowNumber, $totalReservationPrice);                           $sheet->mergeCells('P'.$rowNumber.':P'.($rowNumber + 1));
			
			$grandTotalPax      +=	$totalReservationPax;
			$grandTotalPrice    +=	$totalReservationPrice;
			$rowNumber          +=  2;
			
		}
				
		$sheet->setCellValue('A'.$rowNumber, 'TOTAL');
		$sheet->mergeCells('A'.$rowNumber.':L'.$rowNumber);
		$sheet->mergeCells('N'.$rowNumber.':O'.$rowNumber);
		$sheet->setCellValue('M'.$rowNumber, $grandTotalPax); $sheet->getStyle('M'.$rowNumber)->getAlignment()->setHorizontal('right');	$sheet->getStyle('M'.$rowNumber)->getFont()->setBold( true );
		$sheet->setCellValue('P'.$rowNumber, $grandTotalPrice); $sheet->getStyle('P'.$rowNumber)->getAlignment()->setHorizontal('right');	$sheet->getStyle('P'.$rowNumber)->getFont()->setBold( true );

		$sheet->getStyle('A'.$rowNumber)->getFont()->setBold( true );
		$sheet->getStyle('M'.$firstRowNumber.':M'.$rowNumber)->getFont()->setBold( true );
		$sheet->getStyle('N'.$firstRowNumber.':N'.$rowNumber)->getFont()->setBold( true );
		$sheet->getStyle('O'.$firstRowNumber.':O'.$rowNumber)->getFont()->setBold( true );
		$sheet->getStyle('P'.$firstRowNumber.':P'.$rowNumber)->getFont()->setBold( true );
		$sheet->getStyle('A'.$rowNumber)->getAlignment()->setHorizontal('center');
		$sheet->getStyle('A11:P'.$rowNumber)->applyFromArray($styleArray)->getAlignment()->setVertical('top')->setWrapText(true);
		$sheet->setBreak('A'.$rowNumber, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
		
		$sheet->getColumnDimension('A')->setWidth(18);
		$sheet->getColumnDimension('B')->setWidth(12);
		$sheet->getColumnDimension('C')->setWidth(16);
		$sheet->getColumnDimension('D')->setWidth(16);
		$sheet->getColumnDimension('E')->setWidth(18);
		$sheet->getColumnDimension('F')->setWidth(32);
		$sheet->getColumnDimension('G')->setWidth(32);
		$sheet->getColumnDimension('H')->setWidth(40);
		$sheet->getColumnDimension('I')->setWidth(50);
		$sheet->getColumnDimension('J')->setWidth(10);
		$sheet->getColumnDimension('K')->setWidth(30);
		$sheet->getColumnDimension('L')->setWidth(10);
		$sheet->getColumnDimension('M')->setWidth(12);
		$sheet->getColumnDimension('N')->setWidth(12);
		$sheet->getColumnDimension('O')->setWidth(12);
		$sheet->getColumnDimension('P')->setWidth(12);
		$sheet->setShowGridLines(false);
		
		$sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);

		$writer			=	new Xlsx($spreadsheet);
		$filename		=	'ExcelDetailReservation_'.$partnerName.'_'.$startActivityDate.' - '.$endActivityDate;
		
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
        die;
		
	}

    public function getDetailConfirmReservation()
    {
        helper(['form']);
        $rules      =   [
            'idSchedule'    => ['label' => 'Schedule ID', 'rules' => 'required|alpha_numeric']
        ];

        $messages   =   [
            'idSchedule'    => [
                'required'      => 'Invalid submission data',
                'alpha_numeric' => 'Invalid submission data'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $reservationModel   =   new ReservationModel();
        $idSchedule         =   $this->request->getVar('idSchedule');
        $idSchedule         =   hashidDecode($idSchedule);
        $idPartnerType      =   $this->userData->idPartnerType;
        if(!$idSchedule) return throwResponseNotAcceptable('Invalid submission data');
        $detailData         =	$reservationModel->getDetailDataReservation($idPartnerType, $idSchedule);
        if(!$detailData) return throwResponseNotFound('The reservation details you are looking for could not be found, please refresh data and try again');
        $detailData['IDRESERVATIONDETAILS'] =   hashidEncode($detailData['IDRESERVATIONDETAILS']);

        return $this->setResponseFormat('json')
                    ->respond([
                        "detailData"=>  $detailData,
                        "isDriver"  =>  $idPartnerType == 1 ? false : true
                     ]);
    }

    public function submitReservationConfirmation()
    {
        helper(['form']);
        $rules      =   [
            'idReservationDetails'  => ['label' => 'Reservation Details ID', 'rules' => 'required|alpha_numeric'],
            'idSchedule'            => ['label' => 'Schedule ID', 'rules' => 'required|alpha_numeric'],
            'scheduleTimeHour'      => ['label' => 'Schedule Time (Hour)', 'rules' => 'required|numeric|exact_length[2]'],
            'scheduleTimeMinute'    => ['label' => 'Schedule Time (Minute)', 'rules' => 'required|numeric|exact_length[2]'],
        ];

        $messages   =   [
            'idReservationDetails'  => [
                'required'      => 'Invalid submission data',
                'alpha_numeric' => 'Invalid submission data'
            ],
            'idSchedule'            => [
                'required'      => 'Invalid submission data',
                'alpha_numeric' => 'Invalid submission data'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());

        $idReservationDetails   =   $this->request->getVar('idReservationDetails');
        $idReservationDetails   =   hashidDecode($idReservationDetails);
        $idSchedule             =   $this->request->getVar('idSchedule');
        $idSchedule             =   hashidDecode($idSchedule);
        $scheduleTimeHour       =   $this->request->getVar('scheduleTimeHour');
        $scheduleTimeMinute     =   $this->request->getVar('scheduleTimeMinute');
        $scheduleTime           =   $scheduleTimeHour.":".$scheduleTimeMinute.":00";
        $idPartnerType          =   $this->userData->idPartnerType;
        $userParnerName         =   $this->userData->name;
        $statusProcess          =   $idPartnerType == 1 ? 1 : 0;
        if(!$idSchedule) return throwResponseNotAcceptable('Invalid submission data');
        
        $dataUpdateSchedule =   [
            'USERCONFIRM'       =>  $userParnerName,
            'DATETIMECONFIRM'   =>  $this->currentDateTime,
            'STATUSPROCESS'     =>  $statusProcess,
            'STATUSCONFIRM'     =>  1
        ];

        if($idPartnerType == 1) $dataUpdateSchedule['TIMESCHEDULE'] =   $scheduleTime;
        
        $mainOperation          =   new MainOperation();
        $scheduleModel          =   $idPartnerType == 1 ? new ScheduleVendorModel() : new ScheduleDriverModel();
        $scheduleModel->update($idSchedule, $dataUpdateSchedule);

        $dataStatusProcess      =   $mainOperation->getDataStatusProcess($idPartnerType, 1);
        $statusProcessStr       =	$dataStatusProcess['STATUSPROCESSNAME'];
        $arrInsertTimeline      =	[
            "IDRESERVATIONDETAILS"	=>  $idReservationDetails,
            "DESCRIPTION"			=>  "Booking confirmed and ".$statusProcessStr,
            "DATETIME"				=>  $this->currentDateTime
        ];
        $mainOperation->insertDataTable("t_reservationdetailstimeline", $arrInsertTimeline);

        if(PRODUCTION_URL) $this->updatePartnerRTDBStatisticReservation();

        return throwResponseOK('Reservation confirmation has been received');
    }

    private function updatePartnerRTDBStatisticReservation(){
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
            $referencePartner   =	$database->getReference(FIREBASE_RTDB_MAINREF_NAME.$RTDB_partnerType."/".$RTDB_idUserPartner."/unconfirmedReservation");
            $referencePartnerGet=	$referencePartner->getValue();
            if($referencePartnerGet != null || !is_null($referencePartnerGet)){
                $referencePartner->set([
                    'newReservationStatus'          =>	false,
                    'totalUnconfirmedReservation'   =>  $mainOperation->getTotalUnconfirmedReservation($idPartnerType, $idPartner)
			    ]);
            }
		} catch (\Throwable $th) {
			return true;
		}
	}
}