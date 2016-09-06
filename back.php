<?php

/**
 * @author www.softiran.org
 * @copyright 2016
 */
 
session_start();
function messeg2($result)
{
	switch ($result) 
	{
		case '-20':
				return "در درخواست کارکتر های غیر مجاز وجو دارد";
			break;
			case '-30':
				return " تراکنش قبلا برگشت خورده است";
			break;
			case '-50':
				return " طول رشته درخواست غیر مجاز است";
			break;
			case '-51':
				return " در در خواست خطا وجود دارد";
			break;
			case '-80':
				return " تراکنش مورد نظر یافت نشد";
			break;
			case '-81':
				return " خطای داخلی بانک";
			break;
			case '-90':
				return " تراکنش قبلا تایید شده است";
			break;
	}
}
function messeg($resultCode)
{
	switch ($resultCode) 
	{
		case 110:
				return " انصراف دارنده کارت";
			break;
		case 120:
			return"   موجودی کافی نیست";
			break;
		case 130:
		case 131:
		case 160:
			return"   اطلاعات کارت اشتباه است";
			break;
		case 132:
		case 133:
			return"   کارت مسدود یا منقضی می باشد";
			break;
		case 140:
			return" زمان مورد نظر به پایان رسیده است";
			break;
		case 200:
		case 201:
		case 202:
			return" مبلغ بیش از سقف مجاز";
			break;
		case 166:
			return" بانک صادر کننده مجوز انجام  تراکنش را صادر نکرده";
			break;
		case 150:
		default:
			return " خطا بانک  $resultCode";
		break;
	}
}
function send_mail($to_email,$ref) {
	require_once('class.phpmailer.php');
$message ="<div dir='rtl' >
	پرداخت کامل شده است <br/>
	کد پیگیری : ".$ref."
	</div>";
	$mail = new PHPMailer(true); 
	try {
	$from_name = $_SERVER[HTTP_HOST];
	$from_email = 'info@'.$_SERVER[HTTP_HOST];
	  $mail->AddReplyTo($from_email, $from_name);
	  $mail->SetFrom($from_email, $from_name);
	  $mail->AddAddress($to_email, $to_email);
	  $mail->CharSet = 'UTF-8';
	  $mail->Subject = 'پرداخت کامل شده است ';
	  $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!'; 
	  $mail->MsgHTML($message);

	  $mail->Send();
	  return 1;
	} catch (phpmailerException $e) {
	  echo $e->errorMessage(); 
	} catch (Exception $e) {
	  echo $e->getMessage(); 
	}
}
$res = '<font color="red" ><b>پرداخت شما کامل نشد</b><font>';

$flag = false;
if ($_POST['resultCode'] == '100') 
{
	$referenceId = isset($_POST['referenceId']) ? $_POST['referenceId'] : 0;
	$client = new SoapClient('https://ikc.shaparak.ir/XVerify/Verify.xml', array('soap_version'   => SOAP_1_1));
	$params['token'] =  $_SESSION['token'];
	$params['merchantId'] = $_SESSION['merchantId'];
	$params['referenceNumber'] = $referenceId;
	$params['sha1Key'] = $_SESSION['sha1Key'];
	$result = $client->__soapCall("KicccPaymentsVerification", array($params));
	$result = ($result->KicccPaymentsVerificationResult);
				
	if (floatval($result) > 0 && floatval($result) == floatval($_SESSION['amount']) )
	{	
		//Payment verfed and OK !
		send_mail($_SESSION['email'],$referenceId);
		send_mail($_SESSION['admin_email'],$referenceId);
		$res = '<font color="green" ><b>پرداخت شما کامل شده است</b><font>';
		$msg = 'کد پیگیری : '.$referenceId;
		$flag = true;
	}else
	{
			$msg = messeg2($result);
	}
	
}
else
{
	$msg = messeg($_POST['resultCode']);
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>نتیجه تراکنش</title>
    <style>
    .textbox{
        font:9pt Tahoma;
    }
    table tr td{
        font:9pt Tahoma;
    }
    </style>
</head>
<body style="background-color: #efefef;">
<center>    

        <div style="background-color: #bbe1ff;text-align: center;padding:10px;margin-top:10%;">
        <font color="red" style="font-family: Tahoma;font-size: 13px;"></font>
        <table style="background-color: #fff;" align="center">
            <tr>
                <td colspan="2">
                    <center>
                    <img src="irankish.jpg" />
                    <br />
                    </center>
                </td>
            </tr>
            <tr>
                <td style="text-align: center;" colspan="2">
                   <?php echo $res ; ?>
                </td>
            </tr> 
			<tr>
                <td style="text-align: center;color:<?php if($flag) echo 'green'; else echo 'red'; ?>" colspan="2">
                   <?php echo $msg ; ?>
                </td>
            </tr> 
			<tr>
                <td style="text-align: center;" colspan="2">
                   <a href="./index.php" ><b>برگشت</b></a>
                </td>
            </tr>            
        </table>
        </div>

</center>
<!--//################################## softiran.org ###################################### //-->
</body>
</html>