<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Models\ProductListModel;

class ProductList extends ResourceController
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

    public function getDataProductList()
    {
        $productListModel       =   new ProductListModel();
        $keywordSearch          =   $this->request->getVar('keywordSearch');
        $idVendor               =   $this->userData->idVendor;
        $result                 =	$productListModel->getDataProductList($keywordSearch, $idVendor);

        return $this->setResponseFormat('json')
                    ->respond([
                        "result"    =>  $result
                     ]);
    }
}