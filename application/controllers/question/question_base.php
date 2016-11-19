<?php
    /**
     * 提问基础类
     */
    class Question_base extends MY_Controller
    {
        public function __construct()
        {
            parent::__construct();
        }
        public function index()
        {
            $this->load->view('question/question.html');
        }
    }