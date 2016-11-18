<?php
/**
 * 问题 类
 */
class Quiz extends MY_Controller
{

    public function _remap($class, $args = array()){
        if( method_exists($this, strtolower($class)) ){
            if( count($args) > 0 ){
                $this->$class($args[0]);
            } else {
                $this->$class();
            }
        } else {
            $class_name = 'quiz' . $class;
            $director = dirname(__FILE__) . '\\';

            if( file_exists( $director . $class_name . '.php' ) ){

                include($director . 'quiz_base.php');
                include( $director . $class_name . '.php' );

            } else {
                exit( $director . $class_name . '.php不存在' );
            }

            $object = new $class_name();
            $method_name = 'home';
            if( count($args) > 0 ){
                $method_name = array_shift($args);
            }
            if( count($args) > 0 ){
                $object->$method_name($args);
            } else {
                $object->$method_name();
            }
        }
    }

    /**
     *添加留言
     */
    public function submit ()
    {
        $data = array(
            'title'    => $this->gf('title'),
            'contents'  => $this->gf('contents'),
            'type_id'  => $this->gf('type_id'),
        );

        $error = '';
        if($data['title'] == '' || $data['contents'] == '' || $data['type_id'] == '')
        {
            $error = '输入不完全';
        }
        if($error == '')
        {
            $this->load->library('session');
            $data['username']=$this->session->userdata('username');
            $data['username'] = '陈数';
            if(isset($data['username']))
            {
                $data['create_time'] = time();
                $this -> load -> model('quiz/quiz_model','quiz');
                $a = $this -> quiz -> add($data);
            } else {
                $error = '登录才能发表问题';
            }

        }
        if($error == '')
        {
            echo json_encode(array(
                'type' => 'success'

            ));
        }
    }

    /**
     * 获取问题详细信息
     */
    public function get_quiz()
    {
        $error = '';
        $id = $this -> gf('id');
        $patter = '/^\+?[1-9][0-9]*$/';
        if(preg_match($patter,$id))
        {
            $this -> load -> model('quiz/quiz_model','quiz_model');
            $info = $this -> quiz_model -> details($id);
            if($info['status'] == '0')
            {
                $error = '已删除的问题';
            }
        } else {
            $error = '不正当操作';
        }
        if($error == '')
        {
            echo json_encode(array(
                'type' => 'success',
                'info' => $info
            ));
        } else {
            echo json_encode(array(
                'type'  => 'errpr',
                'error' => $error
            ));
        }
    }

    /**
     * 获取问题列表
     */
    public function get_list()
    {
        $type= $this -> gf('type');
        $data['type'] = $type == ''?'0':$type;
//        $shape = $this -> gf('shape');   //如 solve_1
//        $shape = explode('_',$shape);
//        if(is_array($shape))
//        {
//           $shape = $shape['0'];
//            $data['type']
//        }
        $this->load->model('quiz/quiz_model','quiz_model');
        $this -> quiz_model ->get_list($data);
    }
}