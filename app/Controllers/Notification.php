<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\MessagePartnerModel;

class Notification extends ResourceController
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

    public function getDataNotification()
    {
        helper(['form']);
        $rules      =   [
            'page'  => ['label' => 'Page', 'rules' => 'required|numeric'],
            'status'  => ['label' => 'Status', 'rules' => 'required|numeric']
        ];

        $messages   =   [
            'page'  => [
                'required'=> 'Invalid submission data [1]',
                'numeric' => 'Invalid submission data [2]'
            ],
            'status'  => [
                'required'=> 'Invalid submission data [3]',
                'numeric' => 'Invalid submission data [4]'
            ]
        ];

        if(!$this->validate($rules, $messages)) return $this->fail($this->validator->getErrors());
        
        $messagePartnerModel    =   new MessagePartnerModel();
        $page                   =   $this->request->getVar('page');
        $status                 =   $this->request->getVar('status');
        $idMessagePartnerType   =   $this->request->getVar('idMessagePartnerType');
        $idMessagePartnerType   =   isset($idMessagePartnerType) && $idMessagePartnerType != "" ? hashidDecode($idMessagePartnerType) : "";
        $keywordSearch          =   $this->request->getVar('keywordSearch');
        $idPartnerType          =   $this->userData->idPartnerType;
        $idVendor               =   $this->userData->idVendor;
        $idDriver               =   $this->userData->idDriver;
        $result                 =	$messagePartnerModel->getDataNotification($page, 25, $status, $idMessagePartnerType, $keywordSearch, $idPartnerType, $idVendor, $idDriver);

        return $this->setResponseFormat('json')
                    ->respond([
                        "result"    =>  $result
                     ]);
    }
}