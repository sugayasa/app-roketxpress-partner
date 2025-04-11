<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\I18n\Time;
use App\Models\MainOperation;
use App\Models\FeeModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;

class Fee extends ResourceController
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

    public function getDataDetailFee()
    {
        helper(['form, firebaseJWT']);
        $rules      =   [
            'page'      => ['label' => 'Page', 'rules' => 'required|numeric|min_length[1]'],
            'orderBy'   => ['label' => 'Order By', 'rules' => 'required|numeric|min_length[1]'],
            'orderType' => ['label' => 'Order Type', 'rules' => 'required|in_list[ASC,DESC]'],
            'startDate' => ['label' => 'Start Period', 'rules' => 'required|valid_date[d-m-Y]'],
            'endDate'   => ['label' => 'End Period', 'rules' => 'required|valid_date[d-m-Y]']
        ];

        if(!$this->validate($rules, [])) return $this->fail($this->validator->getErrors());

        $feeModel           =   new FeeModel();
        $page               =   $this->request->getVar('page');
        $orderBy            =   $this->request->getVar('orderBy');
        $orderType          =   $this->request->getVar('orderType');
        $startDate          =   $this->request->getVar('startDate');
        $startDateTF        =   Time::createFromFormat('d-m-Y', $startDate);
        $startDateStr       =   $startDateTF->toDateString();
        $endDate            =   $this->request->getVar('endDate');
        $endDateTF          =   Time::createFromFormat('d-m-Y', $endDate);
        $endDateStr         =   $endDateTF->toDateString();
        $bookingCodeKeyword =   $this->request->getVar('bookingCodeKeyword');
        $productNameKeyword =   $this->request->getVar('productNameKeyword');
        $idPartnerType      =   $this->userData->idPartnerType;
        $idVendor           =   $this->userData->idVendor;
        $idDriver           =   $this->userData->idDriver;
        $idPartner          =   $idPartnerType == 1 ? $idVendor : $idDriver;
        $daysDifference     =   $startDateTF->difference($endDateTF)->getDays();

        if($daysDifference < 0) return throwResponseNotAcceptable("Invalid date selection");
        if($daysDifference > 62) return throwResponseNotAcceptable("Maximum date period is 62 days");
        $orderByStr         =   "";

        switch($orderBy){
            case 2  :   $orderByStr =   "A.JOBTITLE"; break;
            case 1  :   
            default :   $orderByStr =   "B.SCHEDULEDATE"; break;
        }

        $result             =	$feeModel->getDataDetailFee($page, 25, $orderByStr, $orderType, $idPartnerType, $idPartner, $startDateStr, $endDateStr, $bookingCodeKeyword, $productNameKeyword);
        $dataTotal          =   intval($result['dataTotal']);
        $urlExcelDetail     =   '';

        if($dataTotal > 0){
            $arrParamExcelDetail    =   [
                'orderByStr'        =>  $orderByStr,
                'orderType'         =>  $orderType,
                'idPartnerType'		=>  $idPartnerType,
                'idPartner'			=>  $idPartner,
                'startDate'         =>  $startDateStr,
                'endDate'           =>  $endDateStr,
                'bookingCodeKeyword'=>  $bookingCodeKeyword,
                'productNameKeyword'=>  $productNameKeyword
            ];
            $urlExcelDetail         =   BASE_URL."fee/excelDetailFee/".encodeJWTToken($arrParamExcelDetail);

            foreach($result['data'] as $keyResult){
                $keyResult->IDRESERVATIONDETAILS    =   hashidEncode($keyResult->IDRESERVATIONDETAILS);
            }
        }

        return $this->setResponseFormat('json')
                    ->respond([
                        "result"        =>  $result,
                        "urlExcelDetail"=>  $urlExcelDetail
                     ]);
    }
    
    public function excelDetailFee($encryptedParam)
    {
        helper(['firebaseJWT']);

        $mainOperation      =   new MainOperation();
        $feeModel           =   new FeeModel();
		$arrParam           =	decodeJWTToken($encryptedParam);
		$orderByStr         =	$arrParam->orderByStr;
		$orderType          =	$arrParam->orderType;
		$idPartnerType		=	$arrParam->idPartnerType;
		$idPartner			=	$arrParam->idPartner;
		$startDate          =	$arrParam->startDate;
		$endDate            =	$arrParam->endDate;
		$bookingCodeKeyword =	$arrParam->bookingCodeKeyword;
		$productNameKeyword =	$arrParam->productNameKeyword;
		$detailsPartner		=	$idPartnerType == 1 ? $mainOperation->getVendorDetailsById($idPartner) : $mainOperation->getDriverDetailsById($idPartner);
		$detailsPartner		=	isset($idPartner) && $idPartner != "" && $idPartner != 0 ? $detailsPartner : ['NAME' => '-'];
        $partnerName		=   $detailsPartner['NAME'];
        $result             =	$feeModel->getDataDetailFee(1, 999999, $orderByStr, $orderType, $idPartnerType, $idPartner, $startDate, $endDate, $bookingCodeKeyword, $productNameKeyword);
		
		if(count($result['data']) <= 0){
            return throwResponseNotFound("No data found for this action");
		}
		
		$spreadsheet	=	new Spreadsheet();
		$sheet			=	$spreadsheet->getActiveSheet();
		
		$sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
		$sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
		$sheet->getPageMargins()->setTop(0.25)->setRight(0.2)->setLeft(0.2)->setBottom(0.25);
		
		$sheet->setCellValue('A1', 'Bali Sun Tours');
		$sheet->setCellValue('A2', 'Detail Fee');
		$sheet->getStyle('A1:A2')->getFont()->setBold( true );
		$sheet->getStyle('A1:A2')->getAlignment()->setHorizontal('center');
		$sheet->mergeCells('A1:P1')->mergeCells('A2:P2');
		
		$sheet->setCellValue('A4', 'Partner : '.$partnerName);							$sheet->mergeCells('A4:P4');
		$sheet->setCellValue('A5', 'Period : '.$startDate.' - '.$endDate);              $sheet->mergeCells('A5:P5');
		$sheet->setCellValue('A6', 'Booking Code (Contains) : '.$bookingCodeKeyword);   $sheet->mergeCells('A6:P6');
		$sheet->setCellValue('A7', 'Product Name (Contains) : '.$productNameKeyword);   $sheet->mergeCells('A7:P7');
				
		$sheet->setCellValue('A9', 'Date');                     $sheet->mergeCells('A9:A10');
		$sheet->setCellValue('B9', 'Reservation Description');  $sheet->mergeCells('B9:D9');
		$sheet->setCellValue('E9', 'Schedule Details');         $sheet->mergeCells('E9:F9');
		$sheet->setCellValue('G9', 'Adult');                    $sheet->mergeCells('G9:I9');
		$sheet->setCellValue('J9', 'Child');                    $sheet->mergeCells('J9:L9');
		$sheet->setCellValue('M9', 'Infant');                   $sheet->mergeCells('M9:O9');
		$sheet->setCellValue('P9', 'Fee');                      $sheet->mergeCells('P9:P10');
		
		$sheet->setCellValue('B10', 'Source & Booking Code');
		$sheet->setCellValue('C10', 'Title');
		$sheet->setCellValue('D10', 'Guest Name');
		$sheet->setCellValue('E10', 'Product');
		$sheet->setCellValue('F10', 'Notes');
		$sheet->setCellValue('G10', 'Pax');
		$sheet->setCellValue('H10', 'Price Per Pax');
		$sheet->setCellValue('I10', 'Total Price');
		$sheet->setCellValue('J10', 'Pax');
		$sheet->setCellValue('K10', 'Price Per Pax');
		$sheet->setCellValue('L10', 'Total Price');
		$sheet->setCellValue('M10', 'Pax');
		$sheet->setCellValue('N10', 'Price Per Pax');
		$sheet->setCellValue('O10', 'Total Price');
		
		$sheet->getStyle('A9:P10')->getFont()->setBold( true );
		$sheet->getStyle('A9:P10')->getAlignment()->setHorizontal('center');
		$sheet->getStyle('A9:P10')->getAlignment()->setVertical('center');
		$rowNumber			=	$firstRowNumber	=	11;
		$grandTotalAdultPax	=	$grandTotalAdultPrice	=	$grandTotalChildPax	=	$grandTotalChildPrice	=	$grandTotalInfantPax	=	$grandTotalInfantPrice	=	$grandTotalTicketPrice	=	0;
		
		$styleArray = [
			'borders' => [
				'allBorders' => [
					'borderStyle' => Border::BORDER_THIN
				]
			 ]
		];
		
		foreach($result['data'] as $data){
						
			$sheet->setCellValue('A'.$rowNumber, $data->SCHEDULEDATE);
			$sheet->setCellValue('B'.$rowNumber, $data->SOURCENAME." - ".$data->BOOKINGCODE);
			$sheet->setCellValue('C'.$rowNumber, $data->RESERVATIONTITLE);
			$sheet->setCellValue('D'.$rowNumber, $data->CUSTOMERNAME);
			$sheet->setCellValue('E'.$rowNumber, $data->JOBTITLE);
			$sheet->setCellValue('F'.$rowNumber, $data->FEENOTES);
			$sheet->setCellValue('G'.$rowNumber, $data->PAXADULT);
			$sheet->setCellValue('H'.$rowNumber, $data->PRICEPERPAXADULT);
			$sheet->setCellValue('I'.$rowNumber, $data->PRICETOTALADULT);
			$sheet->setCellValue('J'.$rowNumber, $data->PAXCHILD);
			$sheet->setCellValue('K'.$rowNumber, $data->PRICEPERPAXCHILD);
			$sheet->setCellValue('L'.$rowNumber, $data->PRICETOTALCHILD);
			$sheet->setCellValue('M'.$rowNumber, $data->PAXINFANT);
			$sheet->setCellValue('N'.$rowNumber, $data->PRICEPERPAXINFANT);
			$sheet->setCellValue('O'.$rowNumber, $data->PRICETOTALINFANT);
			$sheet->setCellValue('P'.$rowNumber, $data->FEENOMINAL);
			
			$grandTotalAdultPax		+=	$data->PAXADULT;
			$grandTotalChildPax		+=	$data->PAXCHILD;
			$grandTotalInfantPax	+=	$data->PAXINFANT;
			$grandTotalAdultPrice	+=	$data->PRICETOTALADULT;
			$grandTotalChildPrice	+=	$data->PRICETOTALCHILD;
			$grandTotalInfantPrice	+=	$data->PRICETOTALINFANT;
			$grandTotalTicketPrice	+=	$data->FEENOMINAL;
			$rowNumber++;
			
		}
				
		$sheet->setCellValue('A'.$rowNumber, 'TOTAL');
		$sheet->mergeCells('A'.$rowNumber.':F'.$rowNumber);

		$sheet->setCellValue('G'.$rowNumber, $grandTotalAdultPax);		$sheet->getStyle('G'.$rowNumber)->getAlignment()->setHorizontal('right');	$sheet->getStyle('G'.$rowNumber)->getFont()->setBold( true );
		$sheet->setCellValue('I'.$rowNumber, $grandTotalAdultPrice);	$sheet->getStyle('I'.$rowNumber)->getAlignment()->setHorizontal('right');	$sheet->getStyle('I'.$rowNumber)->getFont()->setBold( true );
		$sheet->setCellValue('J'.$rowNumber, $grandTotalChildPax);		$sheet->getStyle('J'.$rowNumber)->getAlignment()->setHorizontal('right');	$sheet->getStyle('J'.$rowNumber)->getFont()->setBold( true );
		$sheet->setCellValue('L'.$rowNumber, $grandTotalChildPrice);	$sheet->getStyle('L'.$rowNumber)->getAlignment()->setHorizontal('right');	$sheet->getStyle('L'.$rowNumber)->getFont()->setBold( true );
		$sheet->setCellValue('M'.$rowNumber, $grandTotalInfantPax);		$sheet->getStyle('M'.$rowNumber)->getAlignment()->setHorizontal('right');	$sheet->getStyle('M'.$rowNumber)->getFont()->setBold( true );
		$sheet->setCellValue('O'.$rowNumber, $grandTotalInfantPrice);	$sheet->getStyle('O'.$rowNumber)->getAlignment()->setHorizontal('right');	$sheet->getStyle('O'.$rowNumber)->getFont()->setBold( true );
		$sheet->setCellValue('P'.$rowNumber, $grandTotalTicketPrice);	$sheet->getStyle('P'.$rowNumber)->getAlignment()->setHorizontal('right');	$sheet->getStyle('P'.$rowNumber)->getFont()->setBold( true );

		$sheet->getStyle('A'.$rowNumber)->getFont()->setBold( true );
		$sheet->getStyle('I'.$firstRowNumber.':I'.$rowNumber)->getFont()->setBold( true );
		$sheet->getStyle('L'.$firstRowNumber.':L'.$rowNumber)->getFont()->setBold( true );
		$sheet->getStyle('N'.$firstRowNumber.':N'.$rowNumber)->getFont()->setBold( true );
		$sheet->getStyle('O'.$firstRowNumber.':O'.$rowNumber)->getFont()->setBold( true );
		$sheet->getStyle('A'.$rowNumber)->getAlignment()->setHorizontal('center');
		$sheet->getStyle('A9:P'.$rowNumber)->applyFromArray($styleArray)->getAlignment()->setVertical('top')->setWrapText(true);
		$sheet->setBreak('A'.$rowNumber, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
		
		$sheet->getColumnDimension('A')->setWidth(12);
		$sheet->getColumnDimension('B')->setWidth(20);
		$sheet->getColumnDimension('C')->setWidth(35);
		$sheet->getColumnDimension('D')->setWidth(30);
		$sheet->getColumnDimension('E')->setWidth(30);
		$sheet->getColumnDimension('F')->setWidth(25);
		$sheet->getColumnDimension('G')->setWidth(8);
		$sheet->getColumnDimension('H')->setWidth(12);
		$sheet->getColumnDimension('I')->setWidth(12);
		$sheet->getColumnDimension('J')->setWidth(8);
		$sheet->getColumnDimension('K')->setWidth(12);
		$sheet->getColumnDimension('L')->setWidth(12);
		$sheet->getColumnDimension('M')->setWidth(8);
		$sheet->getColumnDimension('N')->setWidth(12);
		$sheet->getColumnDimension('O')->setWidth(12);
		$sheet->getColumnDimension('P')->setWidth(12);
		$sheet->setShowGridLines(false);
		
		$sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);

		$writer			=	new Xlsx($spreadsheet);
		$filename		=	'ExcelDetailFeePartner_'.$partnerName.'_'.$startDate.' - '.$endDate;
		
		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
		header('Cache-Control: max-age=0');

		$writer->save('php://output');
        die;		
	}
}