<?php
    /**
     * 自定义核心控制器
     */
    class MY_Controller extends CI_Controller
    {
        public function __construct()
        {
            parent::__construct();
            $this->load->library('encode');

        }
        public function gr($name){
            return $this->encode->get_request_encode($name);
        }

        public function gf($name, $args = array()){
            return $this->encode->getFormEncode($name, true, $args);
        }
    }