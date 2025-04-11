<?php

if(!function_exists('switchMySQLErrorCode')){
    function switchMySQLErrorCode($errorCode){
       switch($errorCode){
            case 0		:	return throwResponseNotModified("No data changes");
                            break;
            case 1062	:	return throwResponseConlflict("There is a duplication of input data");
                            break;
            case 1054	:	return throwResponseInternalServerError("Database internal script error");
                            break;
            default		:	return throwResponseInternalServerError("Unkown database internal error");
                            break;
        }
    }
}