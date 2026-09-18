<?php
defined('BASEPATH') OR exit('No direct script access allowed');
  require FCPATH . 'vendor/autoload.php';

   use Workerman\Worker;
    use Workerman\WebServer;
    use Workerman\Autoloader;
    use PHPSocketIO\SocketIO;

class Socket extends MY_Controller {
 
   function __construct() 
    {
        
        parent::__construct();
        $this->load->model('Query_model');
        $this->load->model('Product_model');
        $this->load->helper('text');        
        $this->TYPE = $this->session->userdata('type');
        $this->LOGIN_ID = ($this->session->userdata($this->TYPE)) ? $this->session->userdata($this->TYPE)['login_id'] : 0;
    }

    public function index($arg=null) 
    {
        $data         = array();
        $data['TYPE'] = $this->TYPE;
    $data['homepage'] = 1;
        $data['csrf'] = csrf_token();
    
        if ($this->uri->segment(2) === FALSE)
        {
            $slug = 0;
        }
        else
        {
            $slug = $this->uri->segment(2);
        }
        
        if(is_api()){
            $slug=$this->input->post('slug');
        }
        
        $args = array('post_slug' => $slug,'enabled' => '1');       
        $detail = $this->Query_model->get_data_obj('ec_page',$args);
     
               if($detail){
            //$detail->post_content = strip_tags($detail->post_content);
            $detail->post_content = $detail->post_content;
               }    
    $crumbs = array("Home" => "/", "$slug" => '');
        $breadcrumbs = $this->breadcrumbs->show_new($crumbs);
        $data['breadcrumbs']    = $breadcrumbs;
    $data['detail'] = $detail;  
    if(is_api()){
             
            unset($data['csrf']);
            unset($data['breadcrumbs']);
            unset($data['TYPE']);
            $response = array(
                'status' => '1',
                'message' => 'success',
             );
            if($detail){
                $response['data'] = [$detail];
            }else{
                $response['data'] = array();
            }
            $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
        }else{
            $this->load->template("/chat/index", $data);
        }
        
        
        
    }

public function server(){

     $io = new SocketIO(2020);
        $io->on('connection', function($socket){
                $socket->addedUser = false;
                // when the client emits 'new message', this listens and executes
                $socket->on('new message', function ($data)use($socket){
                        // we tell the client to execute 'new message'
                        $socket->broadcast->emit('new message', array(
                                    'username'=> $socket->username,
                                    'message'=> $data
                                    ));
                        });

                // when the client emits 'add user', this listens and executes
                $socket->on('add user', function ($username) use($socket){
                        if ($socket->addedUser)
                        return;
                        global $usernames, $numUsers;
                        // we store the username in the socket session for this client
                        $socket->username = $username;
                        ++$numUsers;
                        $socket->addedUser = true;
                        $socket->emit('login', array( 
                                    'numUsers' => $numUsers
                                    ));
                        // echo globally (all clients) that a person has connected
                        $socket->broadcast->emit('user joined', array(
                                    'username' => $socket->username,
                                    'numUsers' => $numUsers
                                    ));
                        });

                // when the client emits 'typing', we broadcast it to others
                $socket->on('typing', function () use($socket) {
                        $socket->broadcast->emit('typing', array(
                                    'username' => $socket->username
                                    ));
                        });

                // when the client emits 'stop typing', we broadcast it to others
                $socket->on('stop typing', function () use($socket) {
                        $socket->broadcast->emit('stop typing', array(
                                    'username' => $socket->username
                                    ));
                        });

                // when the user disconnects.. perform this
                $socket->on('disconnect', function () use($socket) {
                        global $usernames, $numUsers;
                        if($socket->addedUser) {
                        --$numUsers;

                        // echo globally that this client has left
                        $socket->broadcast->emit('user left', array(
                                    'username' => $socket->username,
                                    'numUsers' => $numUsers
                                    ));
                        }
                        });

        });

        if (!defined('GLOBAL_START')) {
            Worker::runAll();
        }    




           }


      

}



