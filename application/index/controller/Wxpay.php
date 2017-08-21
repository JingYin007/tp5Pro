<?php

namespace app\index\controller;
use think\Controller;
use wxpay\database\WxPayResults;
use wxpay\database\WxPayUnifiedOrder;
use wxpay\NativePay;
use wxpay\WxPayApi;
use wxpay\WxPayConfig;

class Wxpay  extends Controller
{

    public function index()
    {
        //TODO 进行业务处理
        //TODO 生成二维码
        $product_id = time()+1;
        $notify = new NativePay();
        $input = new WxPayUnifiedOrder();
        $input->setBody("product_name");
        $input->setAttach("xxx");
        //$input->setOutTradeNo(WxPayConfig::MCHID.date("YmdHis"));
        $input->setOutTradeNo($product_id);
        $input->setTotalFee("1");//以分为单位
        $input->setTimeStart(date("YmdHis"));
        $input->setTimeExpire(date("YmdHis", time() + 600));
        $input->setGoodsTag("test");

        $input->setNotifyUrl(wxPayConfig::NOTIFY_URL);
        $input->setTradeType("NATIVE");
        //$product_id 为商品自定义id 可用作订单ID
        $input->setProductId($product_id);
        $result = $notify->getPayUrl($input);
        if (empty($result['code_url'])){
            $qrCode_url = '';
        }else{
            $qrCode_url = $result["code_url"];
        }

        return $this->fetch('',[
            'qrCode_url' => $qrCode_url,
        ]);
}

    /**
     * 微信支付 回调逻辑处理
     * @return string
     */
    public function notify(){
        $wxData = file_get_contents("php://input");
        //file_put_contents('/tmp/2.txt',$wxData,FILE_APPEND);
        try{
            $resultObj = new WxPayResults();
            $wxData = $resultObj->Init($wxData);
        }catch (\Exception $e){
            $resultObj ->setData('return_code','FAIL');
            $resultObj ->setData('return_msg',$e->getMessage());
            return $resultObj->toXml();
        }

        if ($wxData['return_code']==='FAIL'||
            $wxData['return_code']!== 'SUCCESS'){
            $resultObj ->setData('return_code','FAIL');
            $resultObj ->setData('return_msg','error');
            return $resultObj->toXml();
        }
        //TODO 根据订单号 out_trade_no 来查询订单数据
        $out_trade_no = $wxData['out_trade_no'];
        //此处为举例
        $order = model('order')->get(['out_trade_no' => $out_trade_no]);

        if (!$order || $order->pay_status == 1){
                $resultObj ->setData('return_code','SUCCESS');
                $resultObj ->setData('return_msg','OK');
                return $resultObj->toXml();
        }
        //TODO 数据更新 业务逻辑处理 $order
    }
}
