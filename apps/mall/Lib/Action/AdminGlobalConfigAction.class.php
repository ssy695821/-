<?php
/**
 * 后台积分管理
 * @author wangjun@chuyouyun.com
 * @version chuyouyun2.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminGlobalConfigAction extends AdministratorAction {

    private function __isValidRequest($field, $array = 'post') {
        $field = is_array($field) ? $field : explode(',', $field);
        $array = $array == 'post' ? $_POST : $_GET;
        foreach ($field as $v){
            $v = trim($v);
            if ( !isset($array[$v]) || $array[$v] == '' ) return false;
        }
        return true;
    }

    /**
     * 积分规则设置
     */
    public function credit() {
        $list = M('credit_setting')->where(array('is_open'=>['neq',2]))->order('id ASC')->findPage(20);
        $creditType = M('credit_type')->order('id ASC')->findAll();
        $this->assign('creditType',$creditType);
        $this->assign($list);
        $this->display();
    }

    /**
     * 添加积分规则
     */
    public function addCredit() {
        $creditType = M('credit_type')->order('id ASC')->findAll();//积分类别
        $this->assign('creditType',$creditType);
        $this->assign('type','add');
        $this->display('editCredit');
    }

    /** 系统配置 - 积分配置 **/
    //积分类别设置
    public function creditType(){
        $creditType = M('credit_type')->order('id ASC')->findAll();
        $this->assign('creditType',$creditType);
        $this->display();
    }

    /**
     * 编辑积分类型
     */
    public function editCreditType(){
        $type   = t($_GET['type']);
        if($cid = intval($_GET['cid'])){
            $creditType = M('credit_type')->where("`id`=$cid")->find();//积分类别
            if (!$creditType) $this->error('无此积分类型');
            $this->assign('creditType',$creditType);
        }

        $this->assign('type', $type);

        $typeArr = array('score', 'experience'); //这两个类型的积分类别名不能被修改
        if(in_array($creditType['name'], $typeArr) && $type=='edit'){
            $this->assign('forbidEdit', true);
        }

        $this->display();
    }

    public function doAddCreditType(){
        // if ( !$this->__isValidRequest('name') ) $this->error('数据不完整');
        $name = h(t($_POST['name']));
        $alias=h(t($_POST['alias']));
        if(empty($name) ){
            $this->error('名称不能为空');
        }
        if(empty($alias) ){
            $this->error('别名不能为空');
        }

        $_POST = array_map('t',$_POST);
        $_POST = array_map('h',$_POST);

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '1';
        $data[] = '全局 - 积分配置  - 积分类型';
        if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $res = M('credit_type')->add($_POST);
        if ($res) {
            $db_prefix  = C('DB_PREFIX');
            $model = M('');
            $setting = $model->query("ALTER TABLE {$db_prefix}credit_setting ADD {$_POST['name']} INT(11) DEFAULT 0;");
            $user    = $model->query("ALTER TABLE {$db_prefix}credit_user ADD {$_POST['name']} INT(11) DEFAULT 0;");
            // 清缓存
            F('_service_credit_type', null);
            // 数据表缓存
            D('credit_user')->flush();

            $this->assign('jumpUrl', U('mall/AdminGlobalConfig/creditType'));
            $this->success('保存成功');
        }else {
            $this->error('保存失败');
        }
    }
    public function doEditCreditType(){
        // if ( !$this->__isValidRequest('id,name') ) $this->error('数据不完整');
        $name = h(t($_POST['name']));
        $alias=h(t($_POST['alias']));
        if(empty($name) ){
            $this->error('名称不能为空');
        }
        if(empty($alias) ){
            $this->error('别名不能为空');
        }
        $_POST = array_map('t',$_POST);
        $_POST = array_map('h',$_POST);
        $creditTypeDao = M('credit_type');
        //获取原字段名
        $oldName = $creditTypeDao->find($_POST['id']);

        $typeArr = array('score', 'experience'); //这两个类型的积分类别名不能被修改
        if(in_array($oldName, $typeArr)){
            unset($_POST['name']);
        }

        //修改字段名
        $res = $creditTypeDao->save($_POST);

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '3';
        $data[] = '全局 - 积分配置 - 积分类型 ';
        $data[] = $oldName;
        if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        if ($res) {
            $db_prefix  = C('DB_PREFIX');
            $model = M('');
            $setting = $model->query("ALTER TABLE {$db_prefix}credit_setting CHANGE {$oldName['name']} {$_POST['name']} INT(11);");
            $user    = $model->query("ALTER TABLE {$db_prefix}credit_user CHANGE {$oldName['name']} {$_POST['name']} INT(11);");
            // 清缓存
            F('_service_credit_type', null);
            $this->assign('jumpUrl', U('mall/AdminGlobalConfig/creditType'));
            $this->success('保存成功');
        }else {
            $this->error('保存失败');
        }
    }

    /**
     * 删除积分类型操作
     */
    public function doDeleteCreditType(){
        $ids = t($_POST['ids']);
        $ids = explode(',', $ids);
        if ( empty($ids) ) {echo 0; return ;}

        $map['id'] = array('in', $ids);
        $creditTypeDao = M('credit_type');
        //获取字段名
        $typeName = $creditTypeDao->where($map)->findAll();

        $typeArr = array('score'); // 这两个类型的积分类别名不能被修改
        foreach ( $typeName as $type ) {
            if (in_array ( $type['name'], $typeArr )) {
                echo -2;
                exit;
            }
        }

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '2';
        $data[] = '全局 - 积分配置 - 积分类型 ';
        $data[] = $typeName;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        //清除type信息和对应字段
        $res = M('credit_type')->where($map)->delete();
        if ($res){
            $db_prefix  = C('DB_PREFIX');
            $model = M('');
            foreach($typeName as $v){
                $setting = $model->query("ALTER TABLE {$db_prefix}credit_setting DROP {$v['name']};");
                $user    = $model->query("ALTER TABLE {$db_prefix}credit_user DROP {$v['name']};");
            }
            // 清缓存
            F('_service_credit_type', null);
            echo 1;
        }else{
            echo 0;
        }
    }

    /**
     * 添加积分规则操作
     */
    public function doAddCredit() {
        $name = trim($_POST['name']);
        if($name == "" && $_POST['name'] != ""){
            $this->error('名称不能为空格');
        }
        if ( !$this->__isValidRequest('name') ) $this->error('数据不完整');

        $_POST = array_map('t',$_POST);
        $_POST = array_map('h',$_POST);

        $creditType = M('credit_type')->order('id ASC')->findAll();
        foreach($creditType as $v){
            if(!is_numeric($_POST[$v['name']])){
                $this->error($v['alias'].'的值必须为数字！');
            }
        }

        if($_POST['score'] == 0 ) $this->error('积分增量不能为0!');
        if(preg_match("^[1-9]\d*|0$",$_POST['count'])) $this->error('用户单日执行最大次数必须为整数!');

        dump($_POST);exit;
        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '1';
        $data[] = '全局 - 积分配置 - 积分规则 ';
        if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $res = M('credit_setting')->add($_POST);
        if ($res) {
            // 清缓存
            F('_service_credit_rules', null);
            $this->assign('jumpUrl', U('mall/AdminGlobalConfig/credit'));
            $this->success('保存成功');
        }else {
            $this->error('保存失败');
        }
    }

    /**
     * 编辑积分规则
     */
    public function editCredit() {
        $cid    = intval($_GET['cid']);
        $credit = M('credit_setting')->where("`id`=$cid")->find();
        if (!$credit) $this->error('无此积分规则');

        $creditType = M('credit_type')->order('id ASC')->findAll();//积分类别
        $this->assign('creditType',$creditType);

        $this->assign('credit', $credit);
        $this->assign('type', 'edit');

        $this->display();
    }

    /**
     * 编辑积分规则操作
     */
    public function doEditCredit() {
        $name = trim($_POST['name']);
        if($name == "" && $_POST['name'] != ""){
            $this->error('名称不能为空格');
        }
        if ( !$this->__isValidRequest('id,name') ) $this->error('数据不完整');

        $_POST = array_map('t',$_POST);
        $_POST = array_map('h',$_POST);

        $creditType = M('credit_type')->order('id ASC')->findAll();
        foreach($creditType as $v){
            if(!is_numeric($_POST[$v['name']])){
                $this->error($v['alias'].'的值必须为数字！');
            }
        }

        if($_POST['score'] == 0 ) $this->error('积分增量不能为0!');
        if(preg_match('/^\d+$/',$_POST['count']) == 0) $this->error('用户单日执行最大次数必须为非负整数!');

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '3';
        $data[] = '全局 - 积分配置 - 积分规则 ';
        $credit_info = M('credit_setting')->where('id='.$_POST['id'])->find();
        $data[] = $credit_info;
        $_POST['info'] = $credit_info['info'];
        if( $_POST['__hash__'] )unset( $_POST['__hash__'] );
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);
        $id = $_POST['id'];
        unset($_POST['id']);
        $res = M('credit_setting')->where('id='.$id)->save($_POST);
        if ($res) {
            // 清缓存
            F('_service_credit_rules', null);
            $this->assign('jumpUrl', U('mall/AdminGlobalConfig/credit'));
            $this->success('保存成功');
        }else {
            $this->error('保存失败');
        }
    }

    /**
     * 删除积分规则操作
     */
    public function doDeleteCredit() {
        $ids = t($_POST['ids']);
        $ids = explode(',', $ids);
        if ( empty($ids) ) {echo 0; return ;}

        $map['id'] = array('in', $ids);

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '2';
        $data[] = '全局 - 积分配置 - 积分规则 ';
        $data[] = M('credit_setting')->where($map)->findAll();
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $res = M('credit_setting')->where($map)->delete();
        if ($res) {
            // 清缓存
            F('_service_credit_rules', null);
            echo 1;
        } else {
            echo 0;
        }
    }

    /**
     * 积分等级设置
     */
    public function creditLevel(){
        $_REQUEST['tabHash'] = 'level';
        $this->pageTab[] = array('title'=>L('PUBLIC_SYSTEM_POINT_RULE'),'tabHash'=>'rule','url'=>U('mall/AdminGlobalConfig/credit'));
        $this->pageTab[] = array('title'=>L('PUBLIC_SYSTEM_POINT_TYPE'),'tabHash'=>'type','url'=>U('mall/AdminGlobalConfig/creditType'));
        $this->pageTab[] = array('title'=>L('PUBLIC_SYSTEM_POINT_LEVEL'),'tabHash'=>'level','url'=>U('mall/AdminGlobalConfig/creditLevel'));
        $this->pageTitle[ACTION_NAME] = L('PUBLIC_SYSTEM_POINT_LEVEL');
        $list = model('Credit')->getLevel();
        $this->assign('list',$list);
        $this->display('admin_creditlevel');
    }

    /**
     * 编辑积分等级
     */
    public function setCreditLevel(){
        if(!empty($_POST)){ //添加&编辑积分类型
            $res = model('Credit')->saveCreditLevel($_POST);
            $this->assign('jumpUrl',U('mall/AdminGlobalConfig/creditLevel'));
            $this->success();
            exit();
        }

        $_REQUEST['tabHash'] = 'setCreditLevel';

        $this->pageKeyList = array('level','name','image','start','end');

        $this->pageTab[] = array('title'=>L('PUBLIC_SYSTEM_POINT_RULE'),'tabHash'=>'rule','url'=>U('mall/AdminGlobalConfig/credit'));
        $this->pageTab[] = array('title'=>L('PUBLIC_SYSTEM_POINT_TYPE'),'tabHash'=>'type','url'=>U('mall/AdminGlobalConfig/creditType'));
        $this->pageTab[] = array('title'=>L('PUBLIC_SYSTEM_POINT_LEVEL'),'tabHash'=>'level','url'=>U('mall/AdminGlobalConfig/creditLevel'));

        $this->savePostUrl = U('mall/AdminGlobalConfig/setCreditLevel');
        $list = model('Credit')->getLevel();
        $detailData = $list[intval($_GET['level'])-1];
        $this->pageTitle[ACTION_NAME] = L('PUBLIC_SYSTEM_POINT_EDIT');
        $this->displayConfig($detailData);
    }

    //保存积分规则
    public function savecredit(){
        $res = model('Credit')->saveCreditSet($_POST['creditSet']);
        $this->success();
    }

    public function editCreditLevel(){
        $data['type'] = h($_GET['type']);
        $data['id'] = h($_GET['id']);
        $list = model('Xdata')->get('admin_Credit:level');
        $data['list'] = $list;
        $data['value'] = $list[$data['id']-1];
        $this->assign($data);
        $this->display();
    }


    public function doAddCreditLevel(){
        $start = intval($_POST['start']);
        $end = intval($_POST['end']);
        // if(!$start || !$end){
        //     $this->error('请填写积分值');
        // }
        $name = h(t($_POST['name']));
        // if(empty($name)){
        //     $this->error('请填写等级名称');
        // }

        $list = model('Xdata')->get('admin_Credit:level');
        foreach ($list as $key => $v) {
            $new_list[] = $v;
        }
        $level = count($new_list);
        $value = array();
        $value['level'] = 1;
        $value['start'] = $start;
        $value['end'] = $end;
        $value['name'] = $name;
        $value['image'] = 'level1.png';
        $new_list[$level] = $value;
        $res = model('Xdata')->put('admin_Credit:level',$new_list);
        if($res){
            $this->success('添加成功');
        }
        if(!$res){
            $this->error('添加失败');
        }
    }

    public function doEditCreditLevel(){
        $level = intval($_POST['level']);
        $list = model('Xdata')->get('admin_Credit:level');

        $data = $list;
        $data[$level-1]['start'] = $_POST['start'];
        $data[$level-1]['end'] = $_POST['end'];
        $data[$level-1]['name'] = $_POST['name'];
        $res = model('Xdata')->put('admin_Credit:level',$data);
        if($res){
            $this->success('编辑成功');
        }else{
            $this->error('编辑失败');
        }
    }
    public function doDeleteCreditLevel(){
        $ids = t($_POST['ids']);
        $ids = explode(',', $ids);
        if ( empty($ids) ) {echo 0; return ;}

        $list = model('Xdata')->get('admin_Credit:level');
        foreach ($ids as $key => $value) {
            unset($list[$value-1]);
        }
        echo model('Xdata')->put('admin_Credit:level',$list);
    }

    /*开启/关闭 积分规则*/
    public function openCredit(){
        $ids = t($_POST['ids']);
        $ids = explode(',', $ids);
        if ( empty($ids) ) {echo 0; return ;}

        $map['id'] = array('in', $ids);

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '2';
        $data[] = '全局 - 积分配置 - 积分规则 ';
        $data[] = M('credit_setting')->where($map)->findAll();
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $is_open = M('credit_setting')->where($map)->getField('is_open');
        if($is_open == 1){
            $data['is_open'] = 0;
        }else{
            $data['is_open'] = 1;
        }
        $res = M('credit_setting')->where($map)->save($data);
        if ($res) {
            // 清缓存
            F('_service_credit_rules', null);
            echo 1;
        } else {
            echo 0;
        }
    }
}
