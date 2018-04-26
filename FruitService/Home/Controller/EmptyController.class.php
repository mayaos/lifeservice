<?php
namespace Home\Controller;
use Think\Controller;

class EmptyController extends Controller
{
    function _empty(){
         echo "<script>window.location=history.go(-1)</script>";     
    }
}

?>