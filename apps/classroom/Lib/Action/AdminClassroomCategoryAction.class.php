<?php
/**
 * 云课堂后台配置
 * 分类管理
 * @author ashangmanage <ashangmanage@phpzsm.com>
 * @version CY1.0
 */
tsload(APPS_PATH.'/admin/Lib/Action/AdministratorAction.class.php');
class AdminClassroomCategoryAction extends AdministratorAction
{
	/**
	 * 初始化，配置内容标题
	 * @return void
	 */
	public function _initialize()
	{
		// 管理标题项目
		$this->pageTitle['index'] 			    = '（点播、直播）分类配置';
		$this->pageTitle['packageCategory'] 	= '套餐分类配置';
		$this->pageTitle['libraryCategory']     = '文库分类配置';
		$this->pageTitle['teacherCategory']     = '讲师分类配置';
		$this->pageTitle['schoolCategory']      = '机构分类配置';

		// 管理分页项目
		$this->pageTab[] = array('title'=>$this->pageTitle['index'],'tabHash'=>'index','url'=>U('classroom/AdminClassroomCategory/index'));
		$this->pageTab[] = array('title'=>$this->pageTitle['packageCategory'],'tabHash'=>'packageCategory','url'=>U('classroom/AdminClassroomCategory/packageCategory'));
		$this->pageTab[] = array('title'=>$this->pageTitle['libraryCategory'],'tabHash'=>'libraryCategory','url'=>U('classroom/AdminClassroomCategory/libraryCategory'));
		$this->pageTab[] = array('title'=>$this->pageTitle['teacherCategory'],'tabHash'=>'teacherCategory','url'=>U('classroom/AdminClassroomCategory/teacherCategory'));
		$this->pageTab[] = array('title'=>$this->pageTitle['schoolCategory'],'tabHash'=>'schoolCategory','url'=>U('classroom/AdminClassroomCategory/schoolCategory'));
		parent::_initialize();
	}
	
	//课程(直播、点播)分类列表
	public function index(){
        $treeData = model ( 'CategoryTree' )->setTable ( 'zy_currency_category' )->getNetworkList ();
        $this->displayCoverTree ( $treeData, 'zy_currency_category');
	}
	//套餐分类列表
	public function packageCategory(){
        $treeData = model ( 'CategoryTree' )->setTable ( 'zy_package_category' )->getNetworkList ();
        $this->displayTree ( $treeData, 'zy_package_category',1);
	}
	//文库分类列表
	public function libraryCategory(){
		$treeData = model ( 'CategoryTree' )->setTable ( 'doc_category' )->getNetworkList ();
		$this->displayTree ( $treeData, 'doc_category');
	}

	//讲师分类列表
	public function teacherCategory(){
        $treeData = model ( 'CategoryTree' )->setTable ( 'zy_teacher_category' )->getNetworkList ();
        $this->displayTree ( $treeData, 'zy_teacher_category');
	}

	//机构分类列表
	public function schoolCategory(){
		$treeData = model ( 'CategoryTree' )->setTable ( 'school_category' )->getNetworkList ();
		$this->displayTree ( $treeData, 'school_category');
	}

}