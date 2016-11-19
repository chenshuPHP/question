<?php
/**
 * 问题 类
 */
class Question extends MY_Controller
{

    public function _remap($class, $args = array()){
        if( method_exists($this, strtolower($class)) ){
            if( count($args) > 0 ){
                $this->$class($args[0]);
            } else {
                $this->$class();
            }
        } else {
            $class_name = 'question' . $class;
            $director = dirname(__FILE__) . '\\';

            if( file_exists( $director . $class_name . '.php' ) ){

                include($director . 'question_base.php');
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
    public function index()
    {
        $this->load->view('question/quiz.html');
    }

    /**
     *添加问题
     */
    public function submit ()
    {
        $data = array(
            'token'    => $this->input->post('token'),
            'title'    => $this->input->post('title'),
            'contents' => $this->input->post('contents'),
            'type_id'  => $this->input->post('type_id')
        );
        $error = '';
        $status = $this ->check_token($data['token']);
        if($status['type'] == 'success')
        {
            if(isset($data['title'])  && isset($data['contents']) && isset($data['type_id']))
            {
                if(mb_strlen($data['title']) < 30)
                {
                    $this->load->model('type/type_model');
                   $type = $this -> type_model->check_type($data['type_id']); //true \\ false
                    if($type)
                    {
                        $data['username'] = $status['username'];
                        $data['create_time'] = time();
                        $this -> load -> model('question/question_model','question');
                        $id = $this -> question -> add($data);  //id \\ false
                        if(!$id)
                        {
                            $error = '发布失败';
                        }
                    } else
                    {
                        $error = '类别选择有误';
                    }
                } else {
                    $error = '标题长度过长';
                }

            } else {
                $error = '输入不完全';
            }
        } else {
            $error = '登录才能发表问题';
        }
        if($error == '')
        {
            echo json_encode(array(
               'type' => 'success',
               'id'   => $id
            ));
        } else
        {
            echo json_encode(array(
                'type' => 'error',
                'error'   => $error
            ));
        }
    }

    /**
     * 获取问题详细信息
     */
    public function get_question()
    {

        $id = $this->input->post('id');
        $patter = '/^\+?[1-9][0-9]*$/';
        if(preg_match($patter,$id))
        {
            $this -> load -> model('question/question_model','question_model');
            $info = $this -> question_model -> details($id);
            if($info['status'] == '0')
            {
                $error = '已删除的问题';
            }
        } else {
            $error = '操作有误';
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
        $data['type'] = $this -> gf('type'); //分类id

        if($data['type'] == '')
        {
            $this->load->model('type/type_model','type_model');
            $type_info = $this -> type_model ->type_info($type_id);  //获取分类列表
        }
        $this->load->model('question/question_model','question_model');
        $this->question_model->get_list($type_info);
    }
}