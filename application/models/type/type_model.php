<?php
    class Type_model extends MY_Model
    {
        private $table = 'type';
        public function __construct()
        {
            parent::__construct();
        }

        /**
         * 获取 问题类别信息列表
         * @param $data
         * @param array $field
         * @return array
         */
        public function get_info($type_id,$field = array())
        {
            $config = array('id','pid');
            $field = array_merge($config,$field);
            $infinite = $this -> get_infinite($this->table,$field,'',$type_id);
            $infinite[] = $type_id;
            return $infinite;
        }

        /**
         * 检查分类是否存在
         */
        public function check_type($id)
        {

        }
        /**
         * 删除分类
         */
        public function del($id)
        {
            $data['status'] = '0';
            $result = $this->where('id',$id)
                           ->update('user',$data);
            return $result;
        }

    }