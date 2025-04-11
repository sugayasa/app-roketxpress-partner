<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use CodeIgniter\I18n\Time;

class View extends ResourceController
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
    
    public function dashboard()
    {
        $htmlRes        =   view(
                                'Page/dashboard',
                                ["thisMonth" =>  date('m')],
                                ['debug' => false]
                            );
        return $this->setResponseFormat('json')
        ->respond([
            'htmlRes'   =>  $htmlRes
        ]);
    }
    
    public function notification()
    {
        $htmlRes        =   view(
                                'Page/notification',
                                [],
                                ['debug' => false]
                            );
        return $this->setResponseFormat('json')
        ->respond([
            'htmlRes'   =>  $htmlRes
        ]);
    }
    
    public function productList()
    {
        $htmlRes        =   view(
                                'Page/productList',
                                [],
                                ['debug' => false]
                            );
        return $this->setResponseFormat('json')
        ->respond([
            'htmlRes'   =>  $htmlRes
        ]);
    }
    
    public function reservation()
    {
        $htmlRes        =   view(
                                'Page/reservation',
                                [],
                                ['debug' => false]
                            );
        return $this->setResponseFormat('json')
        ->respond([
            'htmlRes'   =>  $htmlRes
        ]);
    }
    
    public function schedule()
    {
        $arrDates       =   [];
        $dateToday      =   Time::now();
        $arrDates[]     =   [$dateToday->toLocalizedString('d MMM YY'), $dateToday->toLocalizedString('eee'), $dateToday->toLocalizedString('c')];

        for($i=1; $i<=6; $i++){
            $subsDate   =   $dateToday->addDays($i);
            $arrDates[] =   [$subsDate->toLocalizedString('d MMM YY'), $subsDate->toLocalizedString('eee'), $subsDate->toLocalizedString('c')];
        }

        $htmlRes        =   view(
                                'Page/schedule',
                                ['arrHour'=>json_decode(OPTION_HOUR), 'arrDates'=>$arrDates],
                                ['debug' => false]
                            );
        return $this->setResponseFormat('json')
        ->respond([
            'htmlRes'   =>  $htmlRes
        ]);
    }
    
    public function fee()
    {
        $htmlRes        =   view(
                                'Page/fee',
                                [],
                                ['debug' => false]
                            );
        return $this->setResponseFormat('json')
        ->respond([
            'htmlRes'   =>  $htmlRes
        ]);
    }
    
    public function collectPayment()
    {
		$defaultImage   =	URL_COLLECT_PAYMENT_RECEIPT."noimage.jpg";
        $htmlRes        =   view(
                                'Page/collectPayment',
                                ['defaultImage'=>$defaultImage],
                                ['debug' => false]
                            );
        return $this->setResponseFormat('json')
        ->respond([
            'htmlRes'   =>  $htmlRes
        ]);
    }

    public function finance()
    {
        $financeSchemeType  =   $this->userData->financeSchemeType;
        $isDriver           =   $this->userData->idPartnerType == 1 ? false : true;
        $showBtnWithdraw    =   $financeSchemeType == 1 ? true : false;
        $htmlRes            =   view(
                                    'Page/finance',
                                    ['showBtnWithdraw' => $showBtnWithdraw, 'isDriver' => $isDriver],
                                    ['debug' => false]
                                );
        return $this->setResponseFormat('json')
        ->respond([
            'htmlRes'   =>  $htmlRes
        ]);
    }

    public function userFinanceSetting()
    {
        $htmlRes            =   view(
                                    'Page/userFinanceSetting',
                                    [],
                                    ['debug' => false]
                                );
        return $this->setResponseFormat('json')
        ->respond([
            'htmlRes'   =>  $htmlRes
        ]);
    }
}