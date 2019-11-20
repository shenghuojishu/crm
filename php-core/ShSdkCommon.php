<?php
function sdkReturnArr($code=SUCCESS,$data=[],$msg=""){
    return ['code'=>$code,'data'=>$data,'msg'=>$msg];
}
function sdkAjaxApiReturn($code=SUCCESS,$data=[],$msg='',$time=""){
    if ($time) {
        echo json_encode(['code'=>$code,'data'=>$data,'msg'=>$msg,'time'=>$time]);
    }else{
        echo json_encode(['code'=>$code,'data'=>$data,'msg'=>$msg]);
    }
    exit;
}

/**
 * 检查手机号码格式
 * @param $mobile 手机号码
 */
function sdk_check_mobile($mobile)
{
    if (preg_match('/^1[3456789]\d{9}$/', $mobile)) {
        return true;
    }
    return false;
}

/**
 * curl获取请求文本内容
 * @return array
 */
function sdk_https_request($url, $method ='GET', $data = array(),$timeout=300) {
    if ($method == 'POST') {
        //使用crul模拟
        $ch = curl_init();
        //禁用https
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        //允许请求以文件流的形式返回
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);/* 从服务器接收缓冲完成前需要等待多长时间 */
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);/* 在成功连接服务器前等待多久，如果设置为0，则无限等待 */
        curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 30);//设置在内存中保存DNS信息的时间
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch); //执行发送
        curl_close($ch);
    }else {
        if (ini_get('allow_fopen_url') == '1') {
            $context = stream_context_create(array(
                'http' => array(
                    'timeout' => $timeout //超时时间，单位为秒
                )
            ));
            $result = file_get_contents($url,0,$context);
        }else {
            //使用crul模拟
            $ch = curl_init();
            //允许请求以文件流的形式返回
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            //禁用https
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch); //执行发送
            curl_close($ch);
        }
    }
    return $result;
}