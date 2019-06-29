<?php
namespace Pay\Controller;
use Common\Lib\ChannelOrder;
use Think\Exception;
use \Think\Log;
use Common\Lib\ChannelManagerLib;
use Common\Lib\PoolDevLib;

/**
 * Class IndexController
 * @package Pay\Controller
 * @prief 充值接口
 */
class IndexController extends OrderController
{

    protected $member;
    protected $product;
    protected static $RPC_PHONE_URL;

    const RPC_ORDER_API = "/v1/order/pay";

    public function __construct()
    {
        parent::__construct();
        self::$RPC_PHONE_URL = C('RPC_POOL_PHONE');
    }


    public function index() {

        if (!$this->check()) {
            return;
        }

        list($msec, $sec) = explode(' ', microtime());
        $pay_orderid = 'MP' . date('YmdHis',$sec) . intval($msec * 1000);

        $poolLib = new PoolDevLib();

        $phoneRecharger = 'PhoneRechargeDev';

        $notify_url = $this->_site . 'Pay_Notify_Index_Method_' . $phoneRecharger;
        try{
            $c_order = ChannelManagerLib::order( $phoneRecharger,  I('request.'), $notify_url,  $poolLib);

            if ($c_order instanceof ChannelOrder) {

                $order = [
                    'pay_memberid' => $this->member['id'],
                    'pay_orderid' => $pay_orderid,
                    'pay_amount' => round($this->request['pay_amount'] / 100, 2),
                    'pay_applydate' => time(),
                    'pay_code' => $this->request['pay_bankcode'],
                    'pay_notifyurl' => $this->request['pay_notifyurl'],
                    'pay_callbackurl' => $this->request['pay_returnurl'] ?: '',
                    'pay_status' => 0,
                    'out_trade_id' => $this->request['pay_orderid'],
                    'memberid' => $c_order->poolPid,
                    'attach' => $this->request['pay_attach'],
                    'pay_productname' => $this->request['pay_productname'],
                    'pay_url' => $c_order->wapUrl ?: $c_order->qrUrl ?: '',
                    'pool_phone_id' => $c_order->poolId,
                    'trade_id' => $c_order->transID,
                ];

                if (!$this->orderadd($order)) {
                    throw new Exception("订单保存失败");
                }

                $resp = [
                    'orderId' => $pay_orderid,
                    'orderNo' => $this->request['pay_orderid'],
                    'url'     => $c_order->wapUrl,
                    'qr_url'  => $c_order->qrUrl,
                ];
                $this->result_success($resp, "创建订单成功");
                return true;
            }
            throw new Exception("支付Lib返回信息错误");
        } catch(Exception $e){
            Log::write($e->getMessage());
            $poolLib->reset();
            $this->result_error($e->getMessage());
//            $this->result_error('订单生成失败');
            return;
        }
    }


    /**
     * 充值接口
     * 1. 调用接口获取充值的手机和金额
     * 2. 返回接口对外
     */
    public function index2() {
        /**
        pay_memberid

        pay_orderid

        pay_applydate

        pay_bankcode

        pay_notifyurl

        pay_amount

        pay_md5sign

        pay_attach

         */

        // 1. 检查参数, 签名等

        // 2. 调用RPC 获取充值信息

        // 3. 存储订单信息

        // 4. 返回接口内容

        if (!$this->check()) {
            return;
        }

        list($msec, $sec) = explode(' ', microtime());
        $pay_orderid = 'MP' . date('YmdHis',$sec) . intval($msec * 1000);

        $poolOrder = $this->getPoolOrder($pay_orderid);
/*
 *  {
            "pool_id": '',
            "order":  {
                no
                wap_url
                code_url
               }
        }
 */
        if (!$poolOrder) {
            return;
        }

        //  saveorder
        /*

         */

        /*
         *   `pay_memberid` varchar(100) NOT NULL COMMENT '商户编号',
  `pay_orderid` varchar(100) NOT NULL COMMENT '系统订单号',
  `pay_amount` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `pay_actualamount` decimal(15,4) unsigned NOT NULL DEFAULT '0.0000',
  `pay_applydate` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单创建日期',
  `pay_successdate` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '订单支付成功时间',
  `pay_code` varchar(100) DEFAULT NULL COMMENT '支付编码',
  `pay_notifyurl` varchar(500) NOT NULL COMMENT '商家异步通知地址',
  `pay_callbackurl` varchar(500) NOT NULL COMMENT '商家页面通知地址',
  `pay_bankname` varchar(300) DEFAULT NULL,
  `pay_status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '订单状态: 0 未支付 1 已支付未返回 2 已支付已返回',
  `pay_productname` varchar(300) DEFAULT NULL COMMENT '商品名称',
  `pay_zh_tongdao` varchar(50) DEFAULT NULL,
  `out_trade_id` varchar(50) NOT NULL COMMENT '商户订单号',
  `num` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '已补发次数',
  `memberid` varchar(100) DEFAULT NULL COMMENT '支付渠道商家号',
  `account` varchar(100) DEFAULT NULL COMMENT '渠道账号',
  `isdel` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '伪删除订单 1 删除 0 未删',
  `attach` text CHARACTER SET utf8mb4 COMMENT '商家附加字段,原样返回',
  `pay_url` varchar(255) DEFAULT NULL COMMENT '支付地址',
  `pay_channel_account` varchar(255) DEFAULT NULL COMMENT '通道账户',
  `cost` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '成本',
  `cost_rate` decimal(10,4) unsigned NOT NULL DEFAULT '0.0000' COMMENT '成本费率',
  `account_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '子账号id',
  `channel_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '渠道id',
  `last_reissue_time` int(11) NOT NULL DEFAULT '11' COMMENT '最后补发时间',
  `pool_phone_id` int(11) DEFAULT NULL COMMENT '号码池ID',

         */

        $order = [
            'pay_memberid' => $this->member['id'],
            'pay_orderid'  => $pay_orderid,
            'pay_amount'   => round($this->request['pay_amount'] / 100, 2),
            'pay_applydate' => time(),
            'pay_code' => $this->request['pay_bankcode'],
            'pay_notifyurl' => $this->request['pay_notifyurl'],
            'pay_callbackurl' => $this->request['pay_returnurl'] ?: '',
            'pay_status' => 0,
            'out_trade_id' => $this->request['pay_orderid'],
            'memberid' => $poolOrder['pool_pid'],
            'attach' => $this->request['pay_attach'],
            'pay_productname' => $this->request['pay_productname'],
            'pay_url' => $poolOrder['order']['wap_url'] ?: $poolOrder['order']['code_url'] ?: '',
            'pool_phone_id' => $poolOrder['pool_id'],
            'trade_id' => $poolOrder['order']['no'],
        ];

//        $orderModel = M('Order');
//        $r = $orderModel->add($order);
        if (!$this->orderadd($order)){
            $this->result_error("订单保存失败");
            return;
        }


        // return fields

        /*
         *  "orderId":"xxxxx",
        "orderNo":"ddddd",
        "url":"weixin://wxpay/bizpayurl?pr=xxxxx",
        "createTime":"2019-01-11 11:09:03"
         */
        $resp = [
            'orderId' => $pay_orderid,
            'orderNo' => $this->request['pay_orderid'],
            'url'     => $poolOrder['order']['wap_url'],
            'qr_url'  => $poolOrder['order']['code_url']
        ];
        $this->result_success($resp, "创建订单成功");
    }


    // 参数验证 签名验证
    protected function check() {

        $request = $this->request;

        if ( !$request['pay_memberid']
        || !$request['pay_orderid']
        || !$request['pay_applydate']
        || !$request['pay_bankcode']
        || !$request['pay_notifyurl']
        || !$request['pay_amount']
        || !$request['pay_md5sign']
        ){
            $this->result_error("参数不足");
            return;
        }

        $userid = intval($request["pay_memberid"] - 10000); // 商户ID

        $member = M('Member')->where(['id' => $userid])->find();
        if (!$member) {
            $this->result_error('商户不存在');
            return;
        }

        $this->member = $member;


        $this->product = M('Product')->where(['code' => $request['pay_bankcode']])->find();
        if (!$this->product) {
            $this->result_error('支付方式错误');
            return;
        }

        $sign = $request['pay_md5sign'];
//        unset($request['pay_md5sign']);
//        unset($request['pay_attach']);
//        unset($request['pay_productname']);

        $checkParams = [
            "pay_memberid"      =>  $request["pay_memberid"],
            "pay_orderid"       =>  $request["pay_orderid"],
            "pay_amount"        =>  $request["pay_amount"],
            "pay_applydate"     =>  $request["pay_applydate"],
            "pay_bankcode"      =>  $request["pay_bankcode"],
            "pay_notifyurl"     =>  $request["pay_notifyurl"],
            "pay_callbackurl"   =>  $request["pay_callbackurl"],
        ];


        if  (!($sign == createSign( $member['apikey'], $checkParams ))){
            $this->result_error("签名错误");
            return false;
        }

        if (!$this->judgeRepeatOrder()) {
            $this->result_error('重复订单！请尝试重新提交订单');
            return false;
        }

        return true;
    }


    public function judgeRepeatOrder()
    {
        $is_repeat_order = M('Websiteconfig')->getField('is_repeat_order');
        if (!$is_repeat_order) {
            //不允许同一个用户提交重复订单
            $orders = M('Order')->where(['out_trade_id' => $this->request['pay_orderid']])->select();
            $count = 0;

            foreach ($orders as $key => $order) {
                if ($order['pay_memberid'] == $this->member['id']) {
                    $count++;
                }
            }

            if($count){
                return false;
            }
        }
        return true;
    }

    // 获取网厅订单
    protected function getPoolOrder($pay_orderid) {

        $url = self::$RPC_PHONE_URL . self::RPC_ORDER_API;
        $param = [
            'money' => $this->request['pay_amount'],
            'code'  => $this->request['pay_bankcode'],
            'pay_orderid' => $pay_orderid,
            'pay_returnurl' => $this->request['pay_returnurl'],
            'pay_notifyurl' => $this->_site . 'Pay_Notify'
        ];
        $param['sign'] = createSign( C('RPC_POOL_PHONE_SECRET'), $param );

        $data = sendForm($url, $param);
        $data = json_decode($data, true);

        if (!$data || $data['status'] != 0) {
            $this->result_error("RPC ".$data['info'] ?: '订单请求失败');
            return false;
        }

        /**
         * // 外部订单号
        No string `json:"no"`
        // wap地址
        WapUrl string `json:"wap_url"`
        // 扫码地址
        CodeUrl string `json:"code_url"`
         */

        return $data['data'];
    }





}
