<?php

/**
 * Class MY_Model自定义 基础Model 类
 */
    class MY_Model extends CI_Model
    {
        public function __construct()
        {
            parent::__construct();
            $this -> load -> database();
        }
        /**
         * 获取所有要查询的建的所有集合
         */
        public function get_infinite($table,$key,$arr,$pid='0')
        {
            if(is_array($arr))
            {
                $this ->db-> where($arr[0],$arr[1]);
            }
            $array = $this->db-> select($key)->get($table)->result_array();
            return $this->get_child($array,$pid);
        }
        /**
         * 获取子集
         */
        public function get_child($array,$pid)
        {
            static $arr = array();
            foreach($array as $k => $v)
            {
                if($v['pid'] == $pid)
                {
                    $arr[] = $v['id'];
                    $this -> get_child($array,$v['id']);
                }
            }
            return $arr;
        }

    }