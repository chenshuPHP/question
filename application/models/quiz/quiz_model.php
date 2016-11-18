<?php

/**
 * Class Quiz
 */
    class Quiz_model extends MY_Model
    {
        private $table_name = 'quiz';
        public function __construct(){
            parent::__construct();
        }
        /**
         * 提交 问题
         */
        public function add($data)
        {
            if(is_array($data))
            {
                try{
                    $this ->db -> insert($this->table_name,$data);
                    return $this -> db -> insert_id();
                }catch(Exception $e) {
                    throw new Exception($e->getMessage());
                }
            } else {
                return false;
            }

        }
        /**
         * 问题详情页
         */
        public function details($id)
        {
            try{
                $result = $this -> db
                      -> select('username,title,contents,create_time,status,solve,click_rate,type_id')
                      -> where('id',$id)
                      -> get($this -> table_name)
                      -> result_array();
                return $result;
            }catch(Exception $e){
                throw new Exception($e->getMessage());
            }
        }

        /**
         * @param $data 获取问题列表
         */
        public function get_info($data)
        {
            $infinite = $this -> get_infinite('type',array('id','pid'),'',$data['type']);
            $infinite[] = $data['type'];

        }
        public function get_list()
        {

        }


    }