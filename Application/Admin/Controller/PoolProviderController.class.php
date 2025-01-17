<?php
namespace Admin\Controller;
use Think\Page;

class PoolProviderController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

    }

    //列表
    public function index()
    {
        $param = I("get.");
        if(empty($param['status'])){
            $where['status'] = 0;
        }else{
            $where['status'] = array('gt',0);
        }
        if(!empty($param['k'])){
            $where['name|contact|contact_tel'] = $param['k'];
        }

        if(!empty($param['export'])){
            $title = array('商户名称', '联系人', '联系方式', '总收入', '余额', '状态', '创建时间');
            $data = D('PoolProvider')->where($where)->select();
            $list = array();
            foreach ($data as $item) {
                switch ($item['status']) {
                    case 0:
                        $status = '未认证';
                        break;
                    case 1:
                        $status = '正常';
                        break;
                    case 2:
                        $status = '已关闭';
                        break;
                }

                $list[] = array(
                    'name'    => $item['name'],
                    'contact'      => $item['contact'],
                    'contact_tel'     => $item['contact'],
                    'money'    => $item['money'],
                    'balance'      => $item['balance'],
                    'status'  => $status,
                    'create_time' => date('Y-m-d H:i:s', $item['create_time']),
                );
            }

            exportexcel($list, $title);
            exit;
        }

        $data = D('PoolProvider')->getList($where);


        $this->assign('param', $param);
        $this->assign('list', $data['list']);
        $this->assign('page', $data['page']);
        $this->display();
    }


    public function add(){
        if(IS_POST){

            $post=I("post.");
            if(!$post["name"]){
                $this->ajaxReturn(['status'=>0,'msg'=>'请输入名称!']);
            }

            $isHave = M('PoolProvider')->where(['username'=>$post['username']])->find();
            if($isHave){
                $this->ajaxReturn(['status'=>0,'msg'=>'登录用户名已存在']);
            }

            $str = $this->randomStr();

            $data["name"] = $post['name'];
            $data["username"] = $post['username'];
            $data["password"] = md5($post['password']);
            $data["status"] = $post['status'];
            $data["contact"] = $post['contact'];
            $data["contact_tel"] = $post['contact_tel'];
            $data["appkey"] = substr(md5($str), 8, 16);
            $data["appsecret"] = md5($this->randomStr());
            $data["create_time"]=time();
            $data["update_time"]=time();

            $status = D('Common/PoolProvider')->add($data);

            $this->ajaxReturn(['status'=>$status]);

        }else{
            $this->display();
        }
        

    }
    public function delete()
    {    

        $id = I('id', 0, 'intval');
        if(!$id){
            $this->ajaxReturn(['status'=>0,'msg'=>'非法请求!']);
        }
        $where = array(
            'id'     => $id
        );

        $status = D('PoolProvider')->where($where)->delete();
        $this->ajaxReturn(['status'=>$status]);
        
    }

    public function reset(){

        $id = I('id', 0, 'intval');
        if(!$id){
            $this->ajaxReturn(['status'=>0,'msg'=>'非法请求!']);
        }
        $str = $this->randomStr();

        $data["appsecret"] = md5($str);
        $data["update_time"]=time();
        $data["id"]=$id;

        $status = D('Common/PoolProvider')->save($data);
        $this->ajaxReturn(['status'=>$status]);
         
     }

    public function edit(){
       if(IS_POST){

            $data=I("post.");

            if(!$data['id']){
                $this->ajaxReturn(['status'=>0,'msg'=>'非法请求!']);
            }
            if(!$data["name"]){
                $this->ajaxReturn(['status'=>0,'msg'=>'请输入名称!']);
            }
            if(empty($data["password"])){
                unset($data['password']);
            }else{
                $data['password'] = md5($data['password']);
            }
            $data["update_time"]=time();
            $status = D('Common/PoolProvider')->save($data);
            $this->ajaxReturn(['status'=>$status]);

        }else{
            $id = I('id', 0, 'intval');
            if(!$id){
                $this->ajaxReturn(['status'=>0,'msg'=>'非法请求!']);
            }
            $where = array(
                'id'     => $id
            );

            $info = D('PoolProvider')->where($where)->find();
            
            $this->assign('info',$info);
            $this->display();
        }
    }

    public function rate(){
        if(IS_POST){
 
             $data=I("post.");
             if(!$data['id']){
                 $this->ajaxReturn(['status'=>0,'msg'=>'非法请求!']);
             }

            $where = array(
                'id'     => $data['id']
            );

            $info = D('PoolProvider')->where($where)->find();
            $config = json_decode($info['config'],true);
             
            $config['rate'] = $data['rate'];
            $data['config'] = json_encode($config);

             $status = D('Common/PoolProvider')->save($data);
             $this->ajaxReturn(['status'=>$status]);
 
         }else{
             $id = I('id', 0, 'intval');
             if(!$id){
                 $this->ajaxReturn(['status'=>0,'msg'=>'非法请求!']);
             }
             $where = array(
                 'id'     => $id
             );
 
             $info = D('PoolProvider')->where($where)->find();

             $info = json_decode($info['config'],true);

             $sp_list = array('1'=>'移动','2'=>'电信','3'=>'联通');
             $this->assign('sp_list',$sp_list);
             $this->assign('info',$info['rate']);
             $this->assign('id',$id);
             $this->display();
         }
     }

    //列表
    public function order()
    {
        $param = I("get.");
       
        $where['a.pid'] = $param['id'];
        if(!empty($param['pay_memberid'])){
            $where['b.pay_memberid'] = $param['pay_memberid'];
        }
        if(!empty($param['order_id'])){
            $where['a.order_id'] = $param['order_id'];
        }
        if(!empty($param['trade_id'])){
            $where['b.trade_id'] = $param['trade_id'];
        }
        if(!empty($param['phone'])){
            $where['a.phone'] = $param['phone'];
        }
        if(!empty($param['create_time'])){
            $where['b.pay_applydate'] = $param['pay_applydate'];
            list($stime, $etime)  = explode('|', $param['create_time']);
            $where['b.pay_applydate'] = ['between', [strtotime($stime), strtotime($etime) ? strtotime($etime) : time()]];
        }
        if(!empty($param['money'])){
            $where['a.money'] = $param['money']*100;//分
        }
        if(!empty($param['sp'])){
            $where['a.channel'] = $param['sp'];
        }
        if(is_numeric($param['status'])){
            $where['a.status'] = $param['status'];
        }

        $sp_list = array('1'=>'移动','2'=>'电信','3'=>'联通');

        

        if(!empty($param['export'])){
            
            $data = D('PoolProviderSuccess')->getAllList($where);
            $list = array();
            foreach ($data as $item) {
                switch ($item['status']) {
                    case 0:
                        $status = '未回调';
                        break;
                    case 1:
                        $status = '回调成功';
                        break;
                }

                $list[] = array(
                    'order_id'    => $item['order_id'],
                    'trade_id'      => $item['trade_id'],
                    'pool_order_id'     => $item['pool_order_id'],
                    'phone'    => $item['phone'],
                    'money'      => $item['money'],
                    'channel'      => $sp_list[$item['channel']],
                    'pay_name'      => $item['pay_name'],
                    'pay_applydate'      =>date('Y-m-d H:i:s',$item['pay_applydate']),
                    'pay_successdate'      => date('Y-m-d H:i:s',$item['pay_successdate']),
                    'status'  => $status,
                );
            }
            $title = array('平台订单号', '充值流水号', '商户订单号', '手机号', '金额', '运营商', '支付方式', '创建时间', '成功时间', '状态');
            exportexcel($list, $title);
            exit;
            
        }

        $data = D('PoolProviderSuccess')->getList($where);
        

        //交易总额
        $totalWhere['pid'] = $param['id'];
        $money['total'] = D('PoolProviderSuccess')->field('sum(`money`) as money')->where($totalWhere)->find();

        //上月
        $monthWhere['month'] = date('m',strtotime('last month'));
        $monthWhere['pid'] = $param['id'];
        $money['month'] = D('PoolProviderSuccess')->field('sum(`money`) as money')->where($monthWhere)->find();

        //上周
        $sWeek =  date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y")));
        $eweek =  date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y")));
        $weekWhere['time'] = ['between', [strtotime($sWeek), strtotime($eweek)]];
        $weekWhere['pid'] = $param['id'];
        $money['week'] = D('PoolProviderSuccess')->field('sum(`money`) money')->where($weekWhere)->find();
        //今日
        $todayWhere['day'] = date("d");
        $todayWhere['pid'] = $param['id'];
        $money['today'] = D('PoolProviderSuccess')->field('sum(`money`) as money')->where($todayWhere)->find();

        //订单总量
        $money['total']['count'] = D('PoolProviderSuccess')->where($totalWhere)->count();

        //今日订单量
        $money['today']['count'] = D('PoolProviderSuccess')->where($todayWhere)->count();


        

        $this->assign('param', $param);
        $this->assign('count', $money);
        $this->assign('sp_list', $sp_list);
        $this->assign('list', $data['list']);
        $this->assign('page', $data['page']);
        $this->display();

    }


    public function addMoneyLog()
    {
        $param=I('get.');
        if(!empty($param['pid'])){
            $maps['pid'] = $param['uid'];
        }
        $maps['type'] = $param['status'];
        
        $count          = M('PoolMoneychange')->where($maps)->count();

        $size  = 15;
        $rows  = I('get.rows', $size, 'intval');
        if (!$rows) {
            $rows = $size;
        }
        $page           = new Page($count, $rows);
        $list           = M('PoolMoneychange')
            ->where($maps)
            ->limit($page->firstRow . ',' . $page->listRows)
            ->order('id desc')
            ->select();
        $text = [1=>'加款',2=>'减款',3=>'退款'];
        $this->assign("text", $text[$maps['type']]);
        $this->assign("param", $param);
        $this->assign("list", $list);
        $this->assign('page', $page->show());
        $this->display();
    }


    /**
     * 生成随机字符串
     */
    private function randomStr() {
        $year_code = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $order_sn = $year_code[intval(date('Y')) - 2010] .
            strtoupper(dechex(date('m'))) . date('d') .
            substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('d', rand(0, 99));
        return $order_sn;
    }


}
?>
