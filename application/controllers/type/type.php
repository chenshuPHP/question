<?php

/**
 * Class Type 问题分类控制器
 */
    class Type extends CI_Controller
    {
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * 添加分类
         */
        public function add()
        {
            $data = array(
                'token'     => $this->input->post('token'),
                'type_name' => $this->input->post('name'),
                'pid'  => $this->input->post('pid'),

            );
            $error = '';
            $status = $this ->check_token($data['token']);
            if($status['type'] == 'success')
            {
                if(isset($data['type_name']) && isset( $data['paent_id']))
                {
                    $this->load->model('type/type_model');
                    $type = $this -> type_model->check_type(data['type_id']); //true \\ false
                    if($type)
                    {
                        $this->load->model('type/type_model','type_model');
                        $result = $this->type_model->add($data); //true \\ false
                        if(!$result)
                        {
                            $error = '操作出现错误';
                        }
                    } else {
                        $error = '父级选择有误';
                    }
                } else {
                    $error = '输入不完整';
                }
            } else {
                $error = '请管理员登录';
            }
            if($error = '')
            {
                echo json_encode(array(
                    'type' => 'success',
                ));
            } else {
                echo json_encode(array(
                    'type'  => 'error',
                    'error' => $error
                ));
            }
        }

        /**
         * 删除分类
         */
        public function del()
        {
            $data = array(
                'token' => $this->input->post('token'),
                'id'    => $this->input->post('id')
            );
            $error = '';
            $status = $this ->check_token($data['token']);
            if($status['type'] == 'success')
            {
                $preg = '/^\+?[1-9][0-9]*$/';
                if(preg_match($preg,$data['id']))
                {
                    $this->load->model('type/type_model');
                    $result = $this->type_model->del($data['id']);
                    if(!$result)
                    {
                        $error = '操作失败';
                    }
                } else {
                    $error = '操作有误';
                }
            }
        }

        /**
         * 修改类别
         */
        public function edit()
        {
            $data = array(
                'id'         => $this->input->post('id'),
                'pid'        => $this->input->post('pid'),
                'type_name'  => $this->input->post('name'),
            );

        }

    }