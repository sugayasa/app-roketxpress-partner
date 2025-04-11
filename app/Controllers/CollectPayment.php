<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\I18n\Time;
use App\Models\MainOperation;
use App\Models\CollectPaymentModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Kreait\Firebase\Factory;

class CollectPayment extends ResourceController
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

    public function getDataCollectPayment()
    {
        helper(['form, firebaseJWT']);
        $rules      	=   [
            'viewActiveOnly'      => ['label' => 'Active Collect Payment Checkbox', 'rules' => 'is_bool']
        ];
        if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());
        $viewActiveOnly	=   $this->request->getVar('viewActiveOnly');

		if(!$viewActiveOnly){
			$rules      =   [
				'page'      => ['label' => 'Page', 'rules' => 'required|numeric|min_length[1]'],
				'orderBy'   => ['label' => 'Order By', 'rules' => 'required|alpha_numeric'],
				'orderType' => ['label' => 'Order Type', 'rules' => 'required|in_list[ASC,DESC]'],
				'startDate' => ['label' => 'Start Period', 'rules' => 'required|valid_date[d-m-Y]'],
				'endDate'   => ['label' => 'End Period', 'rules' => 'required|valid_date[d-m-Y]']
			];

	        if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());
		}

        $collectPaymentModel=   new CollectPaymentModel();
        $page               =   $this->request->getVar('page');
        $orderBy            =   $this->request->getVar('orderBy');
        $orderBy            =   hashidDecode($orderBy);
        $orderType          =   $this->request->getVar('orderType');
        $startDate          =   $this->request->getVar('startDate');
        $startDateTF        =   Time::createFromFormat('d-m-Y', $startDate);
        $startDateStr       =   $startDateTF->toDateString();
        $endDate            =   $this->request->getVar('endDate');
        $endDateTF          =   Time::createFromFormat('d-m-Y', $endDate);
        $endDateStr         =   $endDateTF->toDateString();
        $collectStatus      =   $this->request->getVar('collectStatus');
        $collectStatus      =   isset($collectStatus) && $collectStatus != "" ? hashidDecode($collectStatus) : "";
        $settlementStatus   =   $this->request->getVar('settlementStatus');
        $settlementStatus   =   isset($settlementStatus) && $settlementStatus != "" ? hashidDecode($settlementStatus) : "";
        $settlementStatus   =   $settlementStatus == 3 ? -1 : $settlementStatus;
        $searchKeyword      =   $this->request->getVar('searchKeyword');
        $idPartnerType      =   $this->userData->idPartnerType;
        $idVendor           =   $this->userData->idVendor;
        $idDriver           =   $this->userData->idDriver;
        $idPartner          =   $idPartnerType == 1 ? $idVendor : $idDriver;
        $daysDifference     =   $startDateTF->difference($endDateTF)->getDays();

        if($daysDifference < 0) return throwResponseNotAcceptable("Invalid date selection");
        if($daysDifference > 62) return throwResponseNotAcceptable("Maximum date period is 62 days");
        $orderByStr         =   "";

        switch($orderBy){
            case 2  :   $orderByStr =   "B.CUSTOMERNAME"; break;
            case 1  :   
            default :   $orderByStr =   "A.DATECOLLECT"; break;
        }

        $result             =	$collectPaymentModel->getDataCollectPayment($page, 25, $orderByStr, $orderType, $idPartnerType, $idPartner, $startDateStr, $endDateStr, $collectStatus, $settlementStatus, $searchKeyword, $viewActiveOnly);
        $dataTotal          =   intval($result['dataTotal']);
        $urlExcelData       =   '';

        if($dataTotal > 0){
            $arrParamExcelData  =   [
                'orderByStr'        =>  $orderBy,
                'orderType'         =>  $orderType,
                'idPartnerType'		=>  $idPartnerType,
                'idPartner'			=>  $idPartner,
                'startDate'         =>  $startDateStr,
                'endDate'           =>  $endDateStr,
                'collectStatus'     =>  $collectStatus,
                'settlementStatus'  =>  $settlementStatus,
                'searchKeyword'     =>  $searchKeyword,
                'viewActiveOnly'	=>  $viewActiveOnly
            ];
            $urlExcelData       =   BASE_URL."collectPayment/excelCollectPayment/".encodeJWTToken($arrParamExcelData);

            foreach($result['data'] as $keyResult){
                $keyResult->IDCOLLECTPAYMENT    =   hashidEncode($keyResult->IDCOLLECTPAYMENT);
            }
        }

        return $this->setResponseFormat('json')
                    ->respond([
                        "result"        =>  $result,
                        "urlExcelData"  =>  $urlExcelData
                     ]);
    }
    
    public function excelCollectPayment($encryptedParam)
    {
        helper(['firebaseJWT']);

        $mainOperation      =   new MainOperation();
        $collectPaymentModel=   new CollectPaymentModel();
		$arrParam           =	decodeJWTToken($encryptedParam);
		$orderByStr         =	$arrParam->orderByStr;
		$orderType          =	$arrParam->orderType;
		$idPartnerType		=	$arrParam->idPartnerType;
		$idPartner			=	$arrParam->idPartner;
		$startDate          =	$arrParam->startDate;
		$endDate            =	$arrParam->endDate;
		$collectStatus      =	$arrParam->collectStatus;
		$settlementStatus   =	$arrParam->settlementStatus;
		$searchKeyword      =	$arrParam->searchKeyword;
		$viewActiveOnly		=	$arrParam->viewActiveOnly;
		$detailsPartner		=	$idPartnerType == 1 ? $mainOperation->getVendorDetailsById($idPartner) : $mainOperation->getDriverDetailsById($idPartner);
		$detailsPartner		=	isset($idPartner) && $idPartner != "" && $idPartner != 0 ? $detailsPartner : ['NAME' => '-'];
        $partnerName		=   $detailsPartner['NAME'];
        $result             =	$collectPaymentModel->getDataCollectPayment(1, 999999, $orderByStr, $orderType, $idPartnerType, $idPartner, $startDate, $endDate, $collectStatus, $settlementStatus, $searchKeyword, $viewActiveOnly);
		
		if(count($result['data']) <= 0){
            return throwResponseNotFound("No data found for this action");
		}

        $strCollectStatus	=	$strSettlementStatus	=	"-";
		switch($collectStatus){
			case "0"	:	$strCollectStatus	=	"Uncollected"; break;
			case "1"	:	$strCollectStatus	=	"Collected"; break;
			default		:	$strCollectStatus	=	"All Collect Status"; break;
		}
		
		switch($settlementStatus){
			case "0"	:	$strSettlementStatus	=	"Unrequested"; break;
			case "1"	:	$strSettlementStatus	=	"Requested"; break;
			case "2"	:	$strSettlementStatus	=	"Approved"; break;
			case "-1"	:	$strSettlementStatus	=	"Rejected"; break;
			default		:	$strSettlementStatus	=	"All Settlement Status"; break;
		}
		
		$spreadsheet	=	new Spreadsheet();
		$sheet			=	$spreadsheet->getActiveSheet();
		
		$sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
		$sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
		$sheet->getPageMargins()->setTop(0.25)->setRight(0.2)->setLeft(0.2)->setBottom(0.25);
		
		$sheet->setCellValue('A1', 'Bali Sun Tours');
		$sheet->setCellValue('A2', 'Data Collect Payment');
		$sheet->getStyle('A1:A2')->getFont()->setBold( true );
		$sheet->getStyle('A1:A2')->getAlignment()->setHorizontal('center');
		$sheet->mergeCells('A1:K1')->mergeCells('A2:K2');
		
		$sheet->setCellValue('A4', 'Partner : '.$partnerName);						$sheet->mergeCells('A4:K4');
		$sheet->setCellValue('A5', 'Period : '.$startDate.' - '.$endDate);          $sheet->mergeCells('A5:K5');
		$sheet->setCellValue('A6', 'Collect Status : '.$strCollectStatus);          $sheet->mergeCells('A6:K6');
		$sheet->setCellValue('A7', 'Settlement Status : '.$strSettlementStatus);    $sheet->mergeCells('A7:K7');
		$sheet->setCellValue('A8', 'Search Keyword : '.$searchKeyword);             $sheet->mergeCells('A7:K7');
				
		$sheet->setCellValue('A10', 'Date');
		$sheet->setCellValue('B10', 'Customer Name');
		$sheet->setCellValue('C10', 'Reservation Title');
		$sheet->setCellValue('D10', 'Reservation Date');
		$sheet->setCellValue('E10', 'Source & Booking Code');
		$sheet->setCellValue('F10', 'Payment Description');
		$sheet->setCellValue('G10', 'Currency');
		$sheet->setCellValue('H10', 'Amount');
		$sheet->setCellValue('I10', 'Amount (IDR)');
		$sheet->setCellValue('J10', 'Status Collect');
		$sheet->setCellValue('K10', 'Status Settlement');
		
		$sheet->getStyle('A10:K10')->getFont()->setBold( true );
		$sheet->getStyle('A10:K10')->getAlignment()->setHorizontal('center')->setVertical('center');
		$rowNumber              =	$firstRowNumber	=	11;
		$grandTotalAmountIDR    =	0;
		
		$styleArray = [
			'borders' => [
				'allBorders' => [
					'borderStyle' => Border::BORDER_THIN
				]
			 ]
		];
        
		foreach($result['data'] as $data){
						
			$grandTotalAmountIDR	+=	$data->AMOUNTIDR;
			$reservationDateEnd		=	"";

			if($data->DURATIONOFDAY > 1){
				$reservationDateEnd	=	" - ".$data->RESERVATIONDATEEND;
			}
						
			$statusCollect	=	$statusSettlement	=	"";
			switch($data->STATUS){
				case "0"	:	$statusCollect	=	'Pending'; break;
				case "1"	:	$statusCollect	=	'Collected'; break;
				case "2"	:	$statusCollect	=	'Deposited'; break;
				default		:	$statusCollect	=	'-'; break;
			}
			
			switch($data->STATUSSETTLEMENTREQUEST){
				case "0"	:	$statusSettlement	=	"Unrequested"; break;
				case "1"	:	$statusSettlement	=	"Requested"; break;
				case "2"	:	$statusSettlement	=	"Approved"; break;
				case "-1"	:	$statusSettlement	=	"Rejected"; break;
				default		:	$statusCollect		=	'-'; break;
			}
            
			$sheet->setCellValue('A'.$rowNumber, $data->DATECOLLECT);
			$sheet->setCellValue('B'.$rowNumber, $data->CUSTOMERNAME);
			$sheet->setCellValue('C'.$rowNumber, $data->RESERVATIONTITLE);
			$sheet->setCellValue('D'.$rowNumber, $data->RESERVATIONDATESTART.$reservationDateEnd);
			$sheet->setCellValue('E'.$rowNumber, $data->SOURCENAME."\n".$data->BOOKINGCODE);
			$sheet->setCellValue('F'.$rowNumber, $data->DESCRIPTION);
			$sheet->setCellValue('G'.$rowNumber, $data->AMOUNTCURRENCY);
			$sheet->setCellValue('H'.$rowNumber, number_format($data->AMOUNT, 0, '.', ',')); $sheet->getStyle('H'.$rowNumber)->getAlignment()->setHorizontal('right');
			$sheet->setCellValue('I'.$rowNumber, number_format($data->AMOUNTIDR, 0, '.', ','));	$sheet->getStyle('I'.$rowNumber)->getAlignment()->setHorizontal('right');
			$sheet->setCellValue('J'.$rowNumber, $statusCollect);
			$sheet->setCellValue('K'.$rowNumber, $statusSettlement);
			$rowNumber++;
			
		}
				
		$sheet->setCellValue('A'.$rowNumber, 'TOTAL');
		$sheet->mergeCells('A'.$rowNumber.':H'.$rowNumber);
		$sheet->getStyle('A'.$rowNumber)->getFont()->setBold( true );
		$sheet->getStyle('A'.$rowNumber)->getAlignment()->setHorizontal('center');
		$sheet->setCellValue('I'.$rowNumber, number_format($grandTotalAmountIDR, 0, '.', ','));	$sheet->getStyle('I'.$rowNumber)->getAlignment()->setHorizontal('right');	$sheet->getStyle('I'.$rowNumber)->getFont()->setBold( true );

		$sheet->getStyle('A10:K'.$rowNumber)->applyFromArray($styleArray)->getAlignment()->setVertical('top')->setWrapText(true);
		$sheet->setBreak('A'.$rowNumber, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);

		$sheet->getColumnDimension('A')->setWidth(12);
		$sheet->getColumnDimension('B')->setWidth(20);
		$sheet->getColumnDimension('C')->setWidth(30);
		$sheet->getColumnDimension('D')->setWidth(16);
		$sheet->getColumnDimension('E')->setWidth(20);
		$sheet->getColumnDimension('F')->setWidth(25);
		$sheet->getColumnDimension('G')->setWidth(9);
		$sheet->getColumnDimension('H')->setWidth(14);
		$sheet->getColumnDimension('I')->setWidth(14);
		$sheet->getColumnDimension('J')->setWidth(12);
		$sheet->getColumnDimension('K')->setWidth(12);
		$sheet->setShowGridLines(false);
		$sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);

		$writer			=	new Xlsx($spreadsheet);
		$filename		=	'ExcelDataCollectPayment_'.$partnerName.'_'.$startDate.' - '.$endDate;
		
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
        die;
	}

	public function searchListReservation()
    {
        helper(['form']);
        $rules      =   [
            'reservationDateStart'	=>	['label' => 'Reservation Date Start', 'rules' => 'required|valid_date[d-m-Y]'],
            'reservationDateEnd'	=>	['label' => 'Reservation Date End', 'rules' => 'required|valid_date[d-m-Y]'],
        ];

        if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());

		$collectPaymentModel	=   new CollectPaymentModel();
        $reservationDateStart	=   $this->request->getVar('reservationDateStart');
        $reservationDateStartTF	=   Time::createFromFormat('d-m-Y', $reservationDateStart);
        $reservationDateStartStr=   $reservationDateStartTF->toDateString();
        $reservationDateEnd		=   $this->request->getVar('reservationDateEnd');
        $reservationDateEndTF	=   Time::createFromFormat('d-m-Y', $reservationDateEnd);
        $reservationDateEndStr	=   $reservationDateEndTF->toDateString();
        $reservationKeyword		=   $this->request->getVar('reservationKeyword');
        $idPartnerType      	=   $this->userData->idPartnerType;
        $idVendor           	=   $this->userData->idVendor;
        $idDriver           	=   $this->userData->idDriver;
        $idPartner          	=   $idPartnerType == 1 ? $idVendor : $idDriver;
        $daysDifference     	=   $reservationDateStartTF->difference($reservationDateEndTF)->getDays();

        if($daysDifference < 0) return throwResponseNotAcceptable("Invalid date selection");
        if($daysDifference > 62) return throwResponseNotAcceptable("Maximum date period is 62 days");

		$result                 =	$collectPaymentModel->getListReservationCollectPayment($idPartnerType, $idPartner, $reservationDateStartStr, $reservationDateEndStr, $reservationKeyword);

		if(!$result) return throwResponseNotFound("No active reservations found based on the date and keyword input");

		foreach($result as $key){
			$key->IDRESERVATION	=   hashidEncode($key->IDRESERVATION);
		}

        return $this->setResponseFormat('json')
                    ->respond([
                        "result"    =>  $result
                     ]);
    }

    public function submitNewCollectPayment()
    {
        helper(['form, database']);
        $rules      =   [
            'idReservation'			=> ['label' => 'Reservation ID', 'rules' => 'required|alpha_numeric'],
            'scheduleDate'			=> ['label' => 'Schedule Date', 'rules' => 'required|valid_date[Y-m-d]'],
            'descriptionPayment'	=> ['label' => 'Payment Description', 'rules' => 'required|alpha_numeric_punct'],
            'paymentCurrency'		=> ['label' => 'Currency', 'rules' => 'required|in_list[USD,IDR]'],
            'paymentAmountInteger'	=> ['label' => 'Amount Integer', 'rules' => 'required|numeric'],
            'paymentAmountDecimal'	=> ['label' => 'Amount Decimal', 'rules' => 'required|numeric']
        ];

        if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());

        $mainOperation		=   new MainOperation();
        $idReservation		=   $this->request->getVar('idReservation');
        $idReservation		=   hashidDecode($idReservation);
        $scheduleDate		=   $this->request->getVar('scheduleDate');
        $description		=   $this->request->getVar('descriptionPayment');
        $currency			=   $this->request->getVar('paymentCurrency');
        $amountInteger		=   $this->request->getVar('paymentAmountInteger');
        $amountDecimal		=   $this->request->getVar('paymentAmountDecimal');
		$amountPayment		=	$amountInteger.".".$amountDecimal;
		$amountPayment		=	$amountPayment * 1;
        $idPartnerType      =   $this->userData->idPartnerType;
        $idVendor           =   $this->userData->idVendor;
        $idDriver           =   $this->userData->idDriver;
		$partnerName		=	$this->userData->partnerName;
		$userPartnerName	=	$this->userData->name;

		if(!$idReservation){
			return throwResponseNotAcceptable("Invalid submission data");
		}

		if($amountPayment <= 0){
			return throwResponseNotAcceptable("Please input valid amount");
		}

		$nomExchangeCurr	=	$currency == "IDR" ? 1 : $mainOperation->getCurrencyExchangeByDate($currency, $scheduleDate);
		$amountPaymentIDR	=	$amountPayment * $nomExchangeCurr;
		$idPaymentMethod	=	$idPartnerType == 1 ? 7 : 2;
		$arrInsertPayment	=	[
			"IDRESERVATION"		=>	$idReservation,
			"IDPAYMENTMETHOD"	=>	$idPaymentMethod,
			"DESCRIPTION"		=>	$description,
			"AMOUNTCURRENCY"	=>	$currency,
			"AMOUNT"			=>	$amountPayment,
			"EXCHANGECURRENCY"	=>	$nomExchangeCurr,
			"AMOUNTIDR"			=>	$amountPaymentIDR,
			"USERINPUT"			=>	$userPartnerName.' (Vendor)',
			"DATETIMEINPUT"		=>	$this->currentDateTime,
			"STATUS"			=>	0,
			"EDITABLE"			=>	1,
			"DELETABLE"			=>	1
		];
		$procInsertPayment	=	$mainOperation->insertDataTable('t_reservationpayment', $arrInsertPayment);
		
		if(!$procInsertPayment['status']) switchMySQLErrorCode($procInsertPayment['errCode']);
		$idReservationPayment	=	$procInsertPayment['insertID'];
		$arrInsertCollect		=	[
			"IDRESERVATION"			=>	$idReservation,
			"IDRESERVATIONPAYMENT"	=>	$idReservationPayment,
			"IDPARTNERTYPE"			=>	$idPartnerType,
			"IDVENDOR"				=>	$idVendor,
			"IDDRIVER"				=>	$idDriver,
			"DATECOLLECT"			=>	$scheduleDate,
			"STATUS"				=>	1,
			"DATETIMEINPUT"			=>	$this->currentDateTime,
			"DATETIMESTATUS"		=>	$this->currentDateTime,
			"LASTUSERINPUT"			=>	$userPartnerName.' (Vendor)'
		];
		
		$procInsertCollect		=	$mainOperation->insertDataTable("t_collectpayment", $arrInsertCollect);
			
		if($procInsertCollect['status']){
			$idCollectPayment		=	$procInsertCollect['insertID'];
			$arrInsertCollectHistory=	[
				"IDCOLLECTPAYMENT"	=>	$idCollectPayment,
				"DESCRIPTION"		=>	"Collect payment is set to ".$partnerName,
				"SETTLEMENTRECEIPT"	=>	"",
				"USERINPUT"			=>	$userPartnerName.' (Vendor)',
				"DATETIMEINPUT"		=>	$this->currentDateTime,
				"STATUS"			=>	0
			];
			$mainOperation->insertDataTable("t_collectpaymenthistory", $arrInsertCollectHistory);
		}

        return throwResponseOK('Addition of your collect payment data has been saved');
	}

	public function getDetailCollectPayment()
    {
        helper(['form, firebaseJWT']);
        $rules      =   [
            'idCollectPayment'   => ['label' => 'Collect Payment ID', 'rules' => 'required|alpha_numeric'],
        ];

        if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());

        $collectPaymentModel    =   new CollectPaymentModel();
        $idCollectPayment       =   $this->request->getVar('idCollectPayment');
        $idCollectPayment       =   hashidDecode($idCollectPayment);

        if(!$idCollectPayment) return throwResponseNotAcceptable("Invalid submission data");

        $detailCollectPayment   =	$collectPaymentModel->getDetailCollectPayment($idCollectPayment);
        if(!$detailCollectPayment) return throwResponseNotFound("The data details you are looking for were not found");

        $historyCollectPayment	=	$collectPaymentModel->getHistoryCollectPayment($idCollectPayment);
		if($historyCollectPayment){
			$lastSettlementReceipt	=	"";
			foreach($historyCollectPayment as $keyHistoryCollectPayment){
				if($keyHistoryCollectPayment->SETTLEMENTRECEIPT != ""){
					$lastSettlementReceipt	=	$keyHistoryCollectPayment->SETTLEMENTRECEIPT;
				}
			}
			if($lastSettlementReceipt != ""){
				$detailCollectPayment['SETTLEMENTRECEIPT']	=	$lastSettlementReceipt;
			}
		}
        unset($detailCollectPayment['IDWITHDRAWALRECAP']);

        return $this->setResponseFormat('json')
                    ->respond([
                        "detailCollectPayment"      =>  $detailCollectPayment,
                        "historyCollectPayment"     =>  $historyCollectPayment
                     ]);
    }   

    public function uploadSettlementReceipt($idCollectPayment)
    {
        if((($_FILES["file"]["type"] == "image/jpeg")
			|| ($_FILES["file"]["type"] == "image/jpg")
			|| ($_FILES["file"]["type"] == "image/png"))
			&& ($_FILES["file"]["size"] <= 500000)){
			if ($_FILES["file"]["error"] > 0) {
				return throwResponseInternalServerError("Failed to upload this file. File is broken");
			}
			
		} else {
			return throwResponseInternalServerError("Failed to upload this file. This file type is not allowed (".$_FILES["file"]["type"].") or file size is too big (".$_FILES["uploaded_file"]["size"].")");
		}
		
		$dir		=	PATH_STORAGE_COLLECT_PAYMENT_RECEIPT;
		$extension	=	pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
		$nameFile	=	"SettlementReceipt"."_".date('YmdHis').".".$extension;
		$move		=	move_uploaded_file($_FILES["file"]["tmp_name"], $dir.$nameFile);
		
		if($move){
            return $this->setResponseFormat('json')
                        ->respond([
                            "status"                    =>  200,
                            "urlSettlementReceipt"      =>  URL_COLLECT_PAYMENT_RECEIPT.$nameFile,
                            "settlementReceiptFileName" =>  $nameFile,
                            "message"                   =>  "File has been uploaded"
                        ]);
		} else {
			return throwResponseInternalServerError("Failed to upload this file. Please try again later");
		}
    } 

    public function submitSettlementCollectPayment()
    {
        helper(['form']);
        $rules      =   [
            'idCollectPayment'          => ['label' => 'Collect Payment ID', 'rules' => 'required|alpha_numeric'],
            'settlementReceiptFileName' => ['label' => 'Settlement Receipt File', 'rules' => 'required|alpha_numeric_punct']
        ];

        if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());

        $mainOperation              =   new MainOperation();
        $collectPaymentModel        =   new CollectPaymentModel();
        $idCollectPayment           =   $this->request->getVar('idCollectPayment');
        $idCollectPayment           =   hashidDecode($idCollectPayment);
        $settlementReceiptFileName  =   $this->request->getVar('settlementReceiptFileName');

        if(!$idCollectPayment) return throwResponseNotAcceptable('Invalid submission data');
		$detailCollectPayment	    =	$collectPaymentModel->getDetailCollectPayment($idCollectPayment);
		
		if(!$detailCollectPayment){
            return throwResponseNotFound('Failed! You are not allowed to perform this action');
		}
        
		$idWithdrawalRecap		=	$detailCollectPayment['IDWITHDRAWALRECAP'];
		$statusCollectPayment	=	$detailCollectPayment['STATUS'];
		$statusSettlementCollect=	$detailCollectPayment['STATUSSETTLEMENTREQUEST'];
		
		if($statusCollectPayment != 1){
            return throwResponseNotAcceptable('Failed! Please confirm that you have received collect payment from order first');
		}

		if($idWithdrawalRecap != 0){
            return throwResponseNotAcceptable('Failed! This collect payment is in the process of being completed along with withdrawal request');
		}
		
		if($statusSettlementCollect != 0 && $statusSettlementCollect != -1){
			switch($statusCollectPayment){
				case "1"	:	return throwResponseNotAcceptable('Failed! Status of settlement collect payment is waiting for approval'); break;
				case "2"	:	return throwResponseNotAcceptable('Failed! Status of settlement collect payment has been completed'); break;
			}
		}

        $arrUpdateCollectPayment	=	[
            "PAYMENTRECEIPTFILENAME"	=>	$settlementReceiptFileName,
            "STATUSSETTLEMENTREQUEST"	=>	1,
            "DATETIMESETTLEMENTREQUEST"	=>	$this->currentDateTime
        ];
        $procUpdateCollectPayment	=	$mainOperation->updateDataTable("t_collectpayment", $arrUpdateCollectPayment, ["IDCOLLECTPAYMENT" => $idCollectPayment]);

        if($procUpdateCollectPayment['status']){
            $idPartnerType          =   $this->userData->idPartnerType;
			$partnerTypeStr         =	$idPartnerType == 1 ? "Vendor" : "Driver";
            $userPartnerName        =   $this->userData->name;
			$arrInsertCollectHistory=	array(
											"IDCOLLECTPAYMENT"	=>	$idCollectPayment,
											"DESCRIPTION"		=>	"Partner requests to validate collect payment ",
											"SETTLEMENTRECEIPT"	=>	$settlementReceiptFileName,
											"USERINPUT"			=>	$userPartnerName." (".$partnerTypeStr.")",
											"DATETIMEINPUT"		=>	$this->currentDateTime,
											"STATUS"			=>	2
										);
			$mainOperation->insertDataTable("t_collectpaymenthistory", $arrInsertCollectHistory);
		}
		
		if(PRODUCTION_URL){
			$partnerName			=	$this->userData->partnerName;
			$totalSettlementRequest	=	$collectPaymentModel->getTotalSettlementRequest();
			$factory				=	(new Factory)->withServiceAccount(FIREBASE_PRIVATE_KEY_PATH)->withDatabaseUri(FIREBASE_RTDB_URI);
			$database				=	$factory->createDatabase();
			$database->getReference(FIREBASE_RTDB_WEBREF_NAME."unprocessedFinanceVendor/collectPayment")
			->set([
				'newCollectPaymentStatus'	=>	true,
				'newCollectPaymentTotal'	=>	$totalSettlementRequest,
				'newCollectPaymentMessage'	=>	"New collect payment settlement request from ".$partnerName,
				'timestampUpdate'			=>	gmdate("YmdHis")
			]);
		}
        return throwResponseOK('Collect payment settlement request has been received. Please wait for validation from the Bali SUN Tours admin finance');
    }
}