<?php

namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use App\Controllers\BaseController;
use App\Models\AccessModel;

class Index extends BaseController
{
    use ResponseTrait;
    public function index()
    {
        return $this->failForbidden('[E-AUTH-000] Forbidden Access');
    }

    public function response404()
    {
        return $this->failNotFound('[E-AUTH-404] Not Found');
    }

    public function main()
    {
        return view('main');
    }

    public function loginPage()
    {
        return view('login');
    }

    public function mainPage()
    {
        helper(['form']);

        $hwid           =   strtoupper($this->request->getVar('hwid'));
        $lastPageAlias  =   strtoupper($this->request->getVar('lastPageAlias'));
        $header         =   $this->request->getServer('HTTP_AUTHORIZATION');
        $explodeHeader  =   $header != "" ? explode(' ', $header) : [];
        $token          =   is_array($explodeHeader) && isset($explodeHeader[1]) && $explodeHeader[1] != "" ? $explodeHeader[1] : "";

        if(isset($token) && $token != ""){
            try {
                $dataDecode         =   decodeJWTToken($token);
                $idPartnerType      =   intval($dataDecode->idPartnerType);
                $idUserPartner      =   intval($dataDecode->idUserPartner);
                $idUserLevelPartner =   intval($dataDecode->idUserLevelPartner);
                $idVendor           =   intval($dataDecode->idVendor);
                $idDriver           =   intval($dataDecode->idDriver);
                $idPartner          =   $idPartnerType == 1 ? $idVendor : $idDriver;
                $hwidToken          =   $dataDecode->hwid;

                if($idUserPartner != 0){
                    if(isset($idUserLevelPartner) && $idUserLevelPartner != "" && $idUserLevelPartner != 0){
                        $accessModel    =   new AccessModel();
                        $partnerDataDB  =   $accessModel->getUserPartnerDetail($idUserPartner);

                        if(!$partnerDataDB || is_null($partnerDataDB)) return $this->failUnauthorized('[E-AUTH-001.1.0] Invalid Token - Not Registered');

                        $hwidDB             =   $partnerDataDB['HWID'];
                        $idUserLevelPartner =   $partnerDataDB['IDUSERLEVELPARTNER'];

                        if($hwid == $hwidDB && $hwid == $hwidToken){
                            $partnerData    =   array(
                                "name"      =>   $partnerDataDB['NAME'],
                                "email"     =>   $partnerDataDB['EMAIL'],
                                "levelName" =>   $partnerDataDB['LEVELNAME']
                            );

                            $listMenuDB         =   $accessModel->getPartnerMenu($idUserLevelPartner);
                            $listMenuGroupDB    =   $accessModel->getPartnerGroupMenu($idUserLevelPartner);
                            $allowNotifList     =   $accessModel->getListNotificationTypeUserLevelPartner($idUserLevelPartner);
                            $menuElement	    =	$this->menuBuilder($listMenuDB, $lastPageAlias, $listMenuGroupDB);
                    		$RTDB_idUserPartner =   $dataDecode->RTDB_idUserPartner;
                            $RTDB_partnerType   =   $idPartnerType == 1 ? 'vendor' : 'driver';
                            $htmlRes            =   view(
                                                        'mainPage',
                                                        array(
                                                            "partnerData"           => $partnerData,
                                                            "menuElement"           => $menuElement,
                                                            "allowNotifList"        => $allowNotifList,
                                                            "optionHour"	        => OPTION_HOUR,
                                                            "optionMinuteInterval"	=> OPTION_MINUTEINTERVAL,
                                                            "optionMonth"	        => OPTION_MONTH,
                                                            "optionYear"	        => OPTION_YEAR,
                                                            "RTDB_idUserPartner"    => $RTDB_idUserPartner,
                                                            "RTDB_partnerType"      => $RTDB_partnerType
                                                        ),
                                                        ['debug' => false]
                                                    );
                            return $this->setResponseFormat('json')
                            ->respond([
                                'htmlRes'   =>  $htmlRes
                            ]);
                        } else {
                            return $this->failUnauthorized('[E-AUTH-001.1.2] Invalid Token - Hardware ID');
                        }
                    } else {
                        return $this->failUnauthorized('[E-AUTH-001.1.3] Invalid Token - Level');
                    }
                } else {
                    return $this->failUnauthorized('[E-AUTH-001.1.4] Invalid Token - Partner ID');
                }
            } catch (\Throwable $th) {
                return $this->failUnauthorized('[E-AUTH-001.2.0] Invalid Token');
            }
        } else {
            return $this->failUnauthorized('[E-AUTH-001.2.0] Invalid Token');
        }
    }

    public function menuBuilder($listMenuDB, $lastPageAlias, $listMenuGroupDB)
    {
        if($listMenuDB == "" || !is_array($listMenuDB) || empty($listMenuDB)){
			return "<li><center>No Menu</center></li>";
		} else {			
			$groupActive	=	0;
			$arrGroupCek	=	array();
			$i				=	0;
			$menuElement	=	$groupActiveName	=	"";
				
			foreach($listMenuGroupDB as $keyMenuGroup){
				$arrGroupCek[]	=	$keyMenuGroup->GROUPNAME;
			}
			
			foreach($listMenuDB as $keyMenu){
				
				if(!in_array($keyMenu->GROUPNAME, $arrGroupCek)){
					
					if($groupActive == 1){
						$groupActive	=	0;
						$menuElement	.=	"</ul></li>";
					}

					$active			=	$lastPageAlias == $keyMenu->MENUALIAS ? "active" : "";
					$menuElement	.=	"<li id='menu".$keyMenu->MENUALIAS."' class='menu-item ".$active."' data-alias='".$keyMenu->MENUALIAS."' data-url='".$keyMenu->URL."'>
											<a href='#'><i class='fa ".$keyMenu->ICON."'></i> <span>".$keyMenu->DISPLAYNAME."</span></a>";
					
				} else {
					
					if($groupActiveName != $keyMenu->GROUPNAME && $groupActiveName != "" && $groupActive == 1){
						$menuElement	.=	"</ul></li>";
					}
					
					if($groupActive == 0 || $groupActiveName != $keyMenu->GROUPNAME){
						$menuElement	.=	"<li class='has-sub-menu'><a href='#'><i class='fa ".$keyMenu->ICON."'></i> <span id='groupMenu".str_replace(" ", "", $keyMenu->GROUPNAME)."'>".$keyMenu->GROUPNAME."</span><span class='menu-expand'><i class='fa fa-chevron-down'></i></span></a><ul class='side-header-sub-menu' style='display: block;'>";
						$groupActive	=	1;
					}
					
					$menuElement	.=	"<li id='menu".$keyMenu->MENUALIAS."' class='menu-item' data-alias='".$keyMenu->MENUALIAS."' data-url='".$keyMenu->URL."'><a href='#'><span>".$keyMenu->DISPLAYNAME."</span></a></li>";
					$groupActiveName=	$keyMenu->GROUPNAME;
				}
				
				$i++;
				
			}
			
			return $menuElement."</ul>";
		}
    }
}
