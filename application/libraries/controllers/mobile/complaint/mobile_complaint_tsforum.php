<?php

/**
 * Class mobile_complaint_tsforum
 * 投诉留言类， ajax交互
 */
class mobile_complaint_tsforum extends mobile_complaint_base
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model('sida/tousu_writeable_model');
    }

    /**
     * @param $path   logo路径
     * @return string
     */
    private function get_logo_complete_url($path)
    {
        return $this -> upload_complete_url( $path, 'logo' );
    }

    /**
     * 给权限信息添加用户信息
     * @param $result    用户对投诉的权限信息
     * @param $username  用户名
     */
    private function _assign_userinfo( &$result, $username )
    {
        $this -> load -> model('company/company');
        $userinfo = $this -> company -> get("select username, company, rejion, logo, register from company where username = '". $username ."'");
        $userinfo['logo_url'] = $this -> get_logo_complete_url( $userinfo['logo'] );
        $result['userinfo'] = $userinfo;
    }

    /**
     * 检查用户是否有权限对投诉进行留言
     */
    public function check_writeable()
    {
        $tsid = $this -> gf('tsid'); //投诉id

        if( $tsid == '' || ! preg_match('/^\d+$/', $tsid) ) show_404();
        $username = $this -> tousu_writeable_model -> get_current_username();
        $result = $this -> tousu_writeable_model -> get_mobile_ive( $username, $tsid );
        $this -> _assign_userinfo($result, $username);
        echo( json_encode( $result ) );
    }

    /**
     * 添加留言
     */
    public function add()
    {
        $error = '';
        // 获取留言信息
        $this -> load -> helper('cookie_helper');
        $config = array(
            'url'		=>$this->get_complete_url('sida/tsforum/add'),
            'data'		=> array(
                'tsid'		         => $this ->gr ('tsid'),
                'detail'		     => $this -> gr('detail'),
                'user_login_token' =>  get_cookie("TOKEN"),
                'token'             => config_item('csrf_token_name')
            )
        );

        $this->load->library('sync');
        $result = $this->sync->ajax( $config );

        print_r( $result);
    }

    /**
     * 读取留言
     */
    public function gets()
    {

        $config = array(
            'page'			=> $this->gr('page'),
            'size'			=> 10,
            'fields'		=> 'id, tsid, username, title, detail, addtime, ive',
            'order'			=> 'order by addtime asc',
            'tsid'			=> $this->gr('tsid'),
            'style'			=> 'pc'
        );
        if( $this->gr('style') != '' ) $config['style'] = $this->gr('style');
        if( $this->gr('size') != '' ) $config['size'] = $this->gr('size');

        switch( $config['style'] )
        {
            case 'mobile':
                $tpl = 'xiehui/tousu/forum_list_segment_mobile.html';
                break;
            default:
                $tpl = 'xiehui/tousu/forum_list_segment.html';
                break;
        }


        if( $config['page'] == '' ) $config['page'] = 1;

        if( $config['tsid'] == '' || ! preg_match('/^\d+$/', $config['tsid']) ) show_404();

        $where = "tsid = '". $config['tsid'] ."'";

        $this->load->model('sida/tousu_forum_model');
        $this->load->model('company/company');

        $sql = "select * from ( select ". $config['fields'] .", num = row_number() over( ". $config['order'] ." ) ".
            "from [". $this->tousu_forum_model->table_name ."] where tsid = '". $config['tsid'] ."' ) ".
            "as tmp where num between ". ( ($config['page'] - 1) * $config['size'] + 1 ) ." and " . ( $config['page'] * $config['size'] );
        $sql_count = "select count(*) as icount from [". $this->tousu_forum_model->table_name ."] where " . $where;

        $res = $this->tousu_forum_model->gets($sql, $sql_count);
        // $this->tousu_forum_model->ive_assign( $res['list'] );
        $this->company->assign( $res['list'], array(
            'fields'		=> 'username, company, rejion, logo, register'
        ) );

        if( $config['style'] == 'mobile' )
        {
            $this->load->library('pagination');
            $this->pagination->currentPage = $config['page'];
            $this->pagination->pageSize = $config['size'];
            $this->pagination->recordCount = $res['count'];
            $urls = config_item('url');
            $this->pagination->url_template = 'javascript:changepage(<{page}>);';
            $this->pagination->url_template_first = 'javascript:changepage(1);';
            $this->tpl->assign('pagination', $this->pagination->tostring_simple( array(
                'select'		=> TRUE,
                'onchange'		=> 'javascript:changepage(this.value);',
                'option_data'	=> TRUE,
                'value'			=> 'page',
                'return'		=> TRUE
            ) ));
        }
        else
        {
            $this->load->library('pagination');
            $this->pagination->currentPage = $config['page'];
            $this->pagination->pageSize = $config['size'];
            $this->pagination->recordCount = $res['count'];
            $urls = config_item('url');
            $this->pagination->url_template = 'javascript:forum.load_forum(<{page}>);';
            $this->pagination->url_template_first = 'javascript:forum.load_forum(1);';
            $this->tpl->assign('pagination', $this->pagination->toString(TRUE));
        }


        $this->tpl->assign('list', $res['list']);
        $content = $this->tpl->fetch( $tpl );

        echo( $content );

    }
}