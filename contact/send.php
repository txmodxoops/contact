<?php
/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * Contact module
 *
 * @copyright   The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license     http://www.fsf.org/copyleft/gpl.html GNU public license
 * @author      Kazumi Ono (aka Onokazu)
 * @author      Trabis <lusopoemas@gmail.com>
 * @author      Hossein Azizabadi (AKA Voltan)
 * @author      Mirza (AKA Bleekk)
 * @version     $Id: index.php 12285 2014-01-30 11:31:16Z beckmi $
 */
include 'header.php';
$xoopsOption['template_main'] = 'contact_index.html';
//unset($_SESSION);
include XOOPS_ROOT_PATH . "/header.php";

/** reCaptcha by google **/
global $xoopsConfig;

if(isset($_POST['g-recaptcha-response'])){
    $captcha=$_POST['g-recaptcha-response'];
}

if(!$captcha && $xoopsModuleConfig['useCaptcha']){
    redirect_header("index.php", 2, _MD_CONTACT_MES_NOCAPTCHA);
}
else {
    $response=file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$xoopsModuleConfig['captchaSecretKey']."&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']);
    if($response.success==false && $xoopsModuleConfig['useCaptcha'])
    {
        redirect_header("index.php", 2, _MD_CONTACT_MES_CAPTCHAINCORRECT);
    }else{

    global $xoopsConfig, $xoopsOption, $xoopsTpl, $xoopsUser, $xoopsUserIsAdmin, $xoopsLogger;
        $op         = $contact_handler->Contact_CleanVars($_POST, 'op', 'form', 'string');
        $department = $contact_handler->Contact_CleanVars($_GET, 'department', '', 'string');
        if($op == "save") {
            if (empty($_POST['submit']) ) {
                redirect_header(XOOPS_URL, 3, _MD_CONTACT_MES_ERROR);
                exit();
            } else {
                    
                // check email
                if (!$contact_handler->Contact_CleanVars($_POST, 'contact_mail', '', 'mail')) {
                    redirect_header("index.php", 1, _MD_CONTACT_MES_NOVALIDEMAIL);
                    exit();
                }
            
                // Info Processing
                $contact = $contact_handler->Contact_InfoProcessing($_POST);
                
                // insert in DB
                if ($saveinfo = true) {
                    $obj = $contact_handler->create();
                    $obj->setVars($contact);
                    if (!$contact_handler->insert($obj)) {
                        redirect_header("index.php", 3, _MD_CONTACT_MES_NOTSAVE);
                        exit();
                       }
                }
            
                // send mail can send message
                if ($sendmail = true) {
                    $message = $contact_handler->Contact_SendMail($contact);
                } elseif ($saveinfo = true) {
                    $message = _MD_CONTACT_MES_SAVEINDB;
                } else {
                    $message = _MD_CONTACT_MES_SENDERROR;
                }
                
                redirect_header(XOOPS_URL, 3, $message);
                exit();
            }
        }

    }
}

include XOOPS_ROOT_PATH . "/footer.php";
