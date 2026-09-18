<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Mailer
{
    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->config('custom_config');
    }

    function mailer_template($args)
    {
        $content = $args['CONTENT'];
        $base_url = $this->ci->config->item('BASE_URL');
        $banner = $base_url.'/assets/images/mailer/banner.jpg';
        $logo_url = $base_url.'/assets/images/logo.png';
        $html =<<<HTML

<!DOCTYPE html>
<html lang="en">
<head>
<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta name="format-detection" content="date=no" />
<meta name="format-detection" content="address=no" />
<meta name="format-detection" content="telephone=no" />
<meta name="x-apple-disable-message-reformatting" />
<!--[if !mso]><!-->
<link href="https://fonts.googleapis.com/css?family=Kreon:400,700|Playfair+Display:400,400i,700,700i|Raleway:400,400i,700,700i|Roboto:400,400i,700,700i" rel="stylesheet" />
<!--<![endif]-->
<title>Email Template</title>
<style type="text/css" media="screen">
/* Linked Styles */
body {
    padding: 0 !important;
    margin: 0 !important;
    display: block !important;
    min-width: 100% !important;
    width: 100% !important;
    -webkit-text-size-adjust: none
}
a {
    color: #000001;
    text-decoration: none
}
p {
    padding: 0 !important;
    margin: 0 !important
}
img {
    -ms-interpolation-mode: bicubic;
}
.text-footer2 a {
    color: #ffffff;
}

/* Mobile styles */
@media only screen and (max-device-width: 480px), only screen and (max-width: 480px) {
.mobile-shell {
    width: 100% !important;
    min-width: 100% !important;
}
.m-center {
    text-align: center !important;
}
.m-left {
    text-align: left !important;
    margin-right: auto !important;
}
.center {
    margin: 0 auto !important;
}

.t-left {
    float: left !important;
    margin-right: 30px !important;
}
.t-left-2 {
    float: left !important;
}
.td {
    width: 100% !important;
    min-width: 100% !important;
}
.content {
    padding: 30px 15px !important;
}
.section {
    padding: 30px 15px 0px !important;
}
.m-br-15 {
    height: 15px !important;
}
.mpb5 {
    padding-bottom: 5px !important;
}
.mpb15 {
    padding-bottom: 15px !important;
}
.mpb20 {
    padding-bottom: 20px !important;
}
.mpb30 {
    padding-bottom: 30px !important;
}
.mp30 {
    padding-bottom: 30px !important;
}
.m-padder {
    padding: 0px 15px !important;
}
.m-padder2 {
    padding-left: 15px !important;
    padding-right: 15px !important;
}
.p70 {
    padding: 30px 0px !important;
}
.pt70 {
    padding-top: 30px !important;
}
.p0-15 {
    padding: 0px 15px !important;
}
.p30-15 {
    padding: 30px 15px !important;
}
.p30-15-0 {
    padding: 30px 15px 0px 15px !important;
}
.p0-15-30 {
    padding: 0px 15px 30px 15px !important;
}
.text-footer {
    text-align: center !important;
}
.m-td, .m-hide {
    display: none !important;
    width: 0 !important;
    height: 0 !important;
    font-size: 0 !important;
    line-height: 0 !important;
    min-height: 0 !important;
}
.m-block {
    display: block !important;
}
.fluid-img img {
    width: 100% !important;
    max-width: 100% !important;
    height: auto !important;
}

}
</style>
</head>
<body class="body"style="padding:0 !important; margin:0 !important; display:block !important; min-width:100% !important; width:100% !important; -webkit-text-size-adjust:none;">
<table width="100%" border="0" cellspacing="0" cellpadding="0"  style="padding-top: 70px;">
  <tr>
    <td align="center" valign="top"><!-- Main -->
      
      <table width="650" border="0" cellspacing="0" cellpadding="0" class="mobile-shell">
        <tr>
          <td class="td" style="width:650px; min-width:650px;border: 1px #c7c7c7 solid; padding:0; margin:0; font-weight:normal;"><!-- Header -->
            
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
              
              <!-- Logo -->
              <tr>
                <td bgcolor="#ffffff" class="p30-15 img-center" style="padding: 30px; border-radius: 20px 20px 0px 0px; font-size:0pt; line-height:0pt; text-align:center;"><a href="#" target="_blank"><img src="https://tarzanmall.kaeizosol.in/assets/images/logo.png"  editable="true" border="0" alt="" style="width: 50%;" /></a></td>
              </tr>
              <!-- END Logo -->
            </table>
            
            <!-- END Header -->
            
            <repeater> 
              <!-- Section 1 -->
              <layout label='Section 1'>
                <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ebebeb">
                  <tr>
                    <td class="p30-15-0" style="padding: 30px 30px 0px;" bgcolor="#ffffff"><table width="100%" border="0" cellspacing="0" cellpadding="0">
                         $content
                         <tr>
                          <td class="text-center"style="color:#5d5c5c; font-family:'Raleway', Arial,sans-serif; font-size:14px; line-height:22px; text-align:left; padding-bottom:40px;"><multiline>If you have any questions,just reply to this email - we're always happy to help out.</multiline></td>
                        </tr>
                        <tr>
                          <td class="h5-center" style="color:#000; font-family:Arial; font-size:16px; line-height:22px; text-align:left; padding-bottom:5px; font-weight:bold;"><multiline> Thanks </multiline></td>
                        </tr>
                         <tr>
                          <td class="text-center"style="color:#5d5c5c; font-family:'Raleway', Arial,sans-serif; font-size:14px; line-height:22px; text-align:left;"><multiline>Tarzan Team</multiline></td>
                        </tr>
                      </table></td>
                  </tr>
                </table>
              </layout>
              <!-- END Section 1 --> 
              
              <!-- White Padder -->
              <layout label='White Padder'>
                <table width="100%" border="0" cellspacing="0" cellpadding="0" bgcolor="#ffffff">
                  
                     <tr>
                       <td class="img mp30" style="padding-top: 40px; font-size:0pt; line-height:0pt; text-align:left;"><multiline></multiline></td>
                   
                  </tr>
                </table>
              </layout>
            </td>
        </tr>
      </table>
      
      <!-- END Main --></td>
  </tr>
</table>
</body>
</html>
HTML;
        return $html;
    }

    function smtp($args)
    {
        $this->ci->load->library('email');

        $subject    = isset($args['SUBJECT']) && $args['SUBJECT'] ? $args['SUBJECT'] : 'Welcome to Cheru';
        $to_email   = isset($args['EMAIL'])   && $args['EMAIL']   ? $args['EMAIL']   : '';
        $from_email = isset($args['FROM'])    && $args['FROM']    ? $args['FROM']    : $this->ci->config->item('FROM_EMAIL');

        $config = array(
            'protocol'  => 'smtp',
            'smtp_host' => $this->ci->config->item('SMTP_HOST'),
            'smtp_port' => $this->ci->config->item('SMTP_PORT'),
            'smtp_user' => $this->ci->config->item('SMTP_USER'),
            'smtp_pass' => $this->ci->config->item('SMTP_PASS'),
            'mailtype'  => 'html',
            'charset'   => 'utf-8',
            'smtp_timeout' => 10,
        );

        try {
            $this->ci->email->initialize($config);
            $this->ci->email->set_mailtype('html');
            $this->ci->email->set_newline("\r\n");

            $htmlContent = $this->mailer_template(array('CONTENT' => $args['CONTENT']));

            $this->ci->email->to($to_email);
            $this->ci->email->from($from_email, 'Cheru');
            $this->ci->email->subject($subject);
            $this->ci->email->message($htmlContent);

            // Suppress fsockopen warnings; log failures instead of echoing them
            $result = @$this->ci->email->send(false);

            if (!$result) {
                $err = $this->ci->email->print_debugger(['headers','subject','body']);
                log_message('error', 'Mailer SMTP failed for [' . $to_email . ']: ' . strip_tags($err));
                return 0;
            }
            return 1;

        } catch (Exception $e) {
            log_message('error', 'Mailer exception for [' . $to_email . ']: ' . $e->getMessage());
            return 0;
        }
    }

}
