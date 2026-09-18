<?php
class Config
{
    protected $DB_NAME = "kaeizner_tm_db";
    protected $DB_USER = "kaeizner_tm_usr";
    protected $DB_PASSWD = "bZktt3fEA4ze";

    protected $DB_HOST_MASTER = "localhost";
    protected $DB_HOST_SLAVE = "localhost";
    protected $DB_HOST_TRACK = "localhost";

    protected $DB_M2I_NAME = "m2i_db";
    protected $DB_M2I_USER = "m2i_usr";
    protected $DB_M2I_PASSWD = "VN&7U*Sd9h";

    protected $REDIS_HOST_MASTER = '127.0.0.1';
    protected $REDIS_PASSWD_MASTER = 'test@1234';

    protected $REDIS_HOST_SLAVE = '127.0.0.1';
    protected $REDIS_PASSWD_SLAVE = 'test@1234';

    function __construct()
    {
        $this->DB_MASTER = $this->dbconn('MASTER');
        $this->DB_SLAVE = $this->dbconn('SLAVE');
    }

    function dbconn($host = 'MASTER')
    {
        $result = Array();
        $db_host = (($host == 'SLAVE') ? $this->DB_HOST_SLAVE : (($host == 'TRACK') ? $this->DB_HOST_TRACK : $this->DB_HOST_MASTER));
        try {
            $dbh = new PDO("mysql:host=".$db_host.";dbname=".$this->DB_NAME, $this->DB_USER, $this->DB_PASSWD);
            // set the PDO error mode to exception
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e)
        {
            $result['error'] = "Connection failed: " . $e->getMessage();
        }

        return $dbh;
    }

    function dbconn_mw($db_host)
    {
        $result = Array();

        try {
            $dbh = new PDO("mysql:host=".$db_host.";dbname=".$this->DB_M2I_NAME, $this->DB_M2I_USER, $this->DB_M2I_PASSWD);
            // set the PDO error mode to exception
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        catch(PDOException $e)
        {
            #$result['error'] = "Connection failed: " . $e->getMessage();
            print "Connection failed: " . $e->getMessage();
        }

        return $dbh;
    }

    function dbconn_redis($host = 'MASTER')
    {
        $redis = new Redis();

        if($host == 'MASTER') {
            $redis->connect($this->REDIS_HOST_MASTER, 6379);
            $redis->auth($this->REDIS_PASSWD_MASTER);
        } else {
            $redis->connect($this->REDIS_HOST_SLAVE, 6379);
            //$redis->auth($this->REDIS_PASSWD_SLAVE);
        }

        return $redis;
    }

    function constants()
    {
        $constants = array(
        'SHORT_DOMAIN' => 'MA',
        'DOMAIN' => 'bl.move2inbox.com',
        'ROOT_DIR' => 'bl',
        'APACHE_USER' => 'bl',
        'FROM_EMAIL' => 'nobody@srv.move2inbox.com',
        'FROM_NAME' => 'MA',
        'INVOICE_DOMAIN' => 'MA',
        'CLIENT_ID' => '640572725018-8grs5s4h3c3tucg8mt6pe4quq1l04mci.apps.googleusercontent.com',
        'CLIENT_SECRET' => 'EexS_iq0siDka9Xc4HFcGYcY',
        'REDIRECT_URI' => 'http://move2inbox.com/gauth.php',
        'REDIRECT_DOMAIN' => 'http://move2inbox.com',
        'DB_HOST'  =>  $this->DB_HOST_MASTER,
        'DB_HOST_MASTER'  =>  $this->DB_HOST_MASTER,
        'DB_HOST_SLAVE'  =>  $this->DB_HOST_SLAVE,
        'DB_HOST_TRACK'  =>  $this->DB_HOST_TRACK,
        'DB_NAME' => $this->DB_NAME,
        'DB_USER' => $this->DB_USER,
        'DB_PASSWD' => $this->DB_PASSWD,
        'DB_BACKUP_PATH' => '/home/m2i/backup/database/',
        'APP_BACKUP_PATH' => '/home/m2i/backup/application/',
        'MAIL_TO' => 'neerajdiwakar@gmail.com',
        'MAIL_TO_A' => 'atul@adsxpedia.com',
        'MAIL_TO_ASM' => 'atul@adsxpedia.com,sanjay@move2inbox.in,manoj@shopatbest.com',
        'MAIL_HEADERS' => 'From: MV <nobody@srv.move2inbox.com>',
        'DOC_ROOT_PATH'  =>  '/home/bl/public_html/',
        'DOWNLOAD_ZIP' => '/home/bl/public_html/mail/downloads/',
        'DOMAIN_NAME'  =>  'http://bl.move2inbox.com/',
        'FEEDBACK_ROOT_PATH' => '/home/bl/mail/move2inbox.com/',
        'GAUTH_VENDOR' => '/home/bl/public_html/application/vendor/autoload.php',
        'DELIVERY_LOG_PATH' => '/home/bl/subscriber-list/',
        'SOLR_HOST' => 'http://192.168.1.4:8983',
        );

        return $constants;
    }

    function select($args)
    {
        $dbh = $args->DBH;
        $sql = $args->QRY;
        $sth = isset($args->STH) ? $args->STH : $dbh->prepare($sql);
        if(isset($args->PARAMS) && is_array($args->PARAMS)){
            try {
                $sth->execute($args->PARAMS);
            }catch(Exception $e) {
                $this->send_mail('Query Error',$e->getMessage()." Query: $sql");
            }
        }else{
            try {
                $sth->execute();
            }catch(Exception $e) {
                $this->send_mail('Query Error',$e->getMessage()." Query: $sql");
            }
        }
        $data = $sth->fetchALL(PDO::FETCH_OBJ);
        return $data;
    }

    function insert($args)
    {
        $dbh = $args->DBH;
        $sql = $args->QRY;
        $sth = isset($args->STH) ? $args->STH : $dbh->prepare($sql);
        if(isset($args->PARAMS) && is_array($args->PARAMS)){
            try {
                $sth->execute($args->PARAMS);
            }catch(Exception $e) {
                $this->send_mail('Query Error',$e->getMessage()." Query: $sql");
            }
        }else{
            try {
                $sth->execute();
            }catch(Exception $e) {
                $this->send_mail('Query Error',$e->getMessage()." Query: $sql");
            }
        }
        $new_row_id = $dbh->lastInsertId();
        return $new_row_id;
    }

    function update($args)
    {
        $dbh = $args->DBH;
        $sql = $args->QRY;
        $sth = isset($args->STH) ? $args->STH : $dbh->prepare($sql);
        if(isset($args->PARAMS) && is_array($args->PARAMS)){
            try {
                $sth->execute($args->PARAMS);
            }catch(Exception $e) {
                $this->send_mail('Query Error',$e->getMessage()." Query: $sql");
            }
        }else{
            try {
                $sth->execute();
            }catch(Exception $e) {
                $this->send_mail('Query Error',$e->getMessage()." Query: $sql");
            }
        }
        $affected_rows = $sth->rowCount();
        return $affected_rows;
    }
    
    function send_mail($subject = NULL, $mail_body = NULL)
    {
        $CONFIG = $this->constants();
        $subject .= "(".gethostname()."): " . $this->get_time();
        $mail_body .= "\n\n".$_SERVER['PHP_SELF'];
        $mail_body .= "\n\nADSXPEDIA Team";

        mail($CONFIG['MAIL_TO'],$subject,$mail_body,$CONFIG['MAIL_HEADERS']);
    }

    function get_line()
    {
        return "<br>====================================<br>";
    }

    function get_time()
    {
        return date('Y-m-d H:i:s');
    }

}
?>
