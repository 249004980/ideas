<?php
/**
 * @Author: JasonX
 * @Description: 获取远程服务器传来的邮件列表
 * @Date:   2015-09-21 14:07:11
 * @Last Modified by:   JasonX
 * @Last Modified time: 2016-06-17 12:22:56
 */
if(isAjax() && validReference()){
  if(isset($_POST['token']) && !isset($_POST['access_token'])){
    $filename = __DIR__.'/'.$_POST['token'].'.log';
    if(file_exists($filename)){
      $res['res'] = 'success';
      $res['email'] = handleEmail($filename);
      echo json_encode($res);die;
    }else{
      $res['res'] = 'error';
      $res['describe'] = 'Email list is not found!';
      echo json_encode($res);die;
    }
    //表示用于获取需要显示在前台页面的邮件列表
    // $fileExist = false;
    // foreach (glob(__DIR__.'/*.log') as $filename) {
    //   if($_POST['token'] == pathinfo($filename,PATHINFO_FILENAME)){
    //     $fileExist = true;
    //     // $list = file_get_contents($filename,PATHINFO_BASENAME);
    //     $res['res'] = 'success';
    //     $res['email'] = handleEmail($filename);
    //     // $res['email'] = $list;
    //     echo json_encode($res);die;
    //   }
    // }
    // if(!$fileExist){
    //   $res['res'] = 'error';
    //   $res['describe'] = 'Email list is not found!';
    //   echo json_encode($res);die;
    // }
  }else{
    echo 400;die;
  }
}else if(isPost() && validReference()){
  $res = [];
  if(isset($_POST['token']) && isset($_POST['email']) && isset($_POST['time']) && isset($_POST['access_token']) && $_POST['access_token']==hash_hmac('md5',$_POST['time'].$_POST['token'],'~\bp4f$,L=Ir)f-k')){
    //传来的值包含活动token(命名log文件),发送的邮箱,发送时间,用于验证的加密字符串
    //生成log文件
    file_put_contents($_POST['token'].'.log', json_encode($_POST['email']));
    echo 200;die;
  }else{
    echo 400;die;
  }
}else{
  return 400;
}

//判断是否为ajax提交
function isAjax(){
    if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
        return true;
    }else{
        return false;
    }
}

//判断是否为post提交
function isPost(){
  if(isset($_SERVER['REQUEST_METHOD']) && strcasecmp($_SERVER['REQUEST_METHOD'],'POST')==0){
    return true;
  }else{
    return false;
  }
}

function handleEmail($file){
    $handle = json_decode(file_get_contents($file,PATHINFO_FILENAME),true);
    $temp = [];
    $str = '';
    foreach ($handle as $k => $v) {
        if(stripos($v,'%digiarty%')){
            $arr = explode('%digiarty%', $v);
            $candidate = $arr[1];
            $v = $arr[0];
        }
        $temp = explode('@',$v);
        if(strlen($temp[0]) <= 5 && strlen($temp[0]) > 2){
            $num = 2;
        }else if(strlen($temp[0]) > 5){
            $num = 5;
        }else{
            $num = strlen($temp[0]);
        }
        $str = substr($temp[0],0,(strlen($temp[0])-$num));
        for($i=0;$i<$num;$i++){
            $str .= '*';
        }
        if(isset($candidate)){
            $handle[$k] = $candidate.' '.$str.'@'.$temp[1];
        }else{
            $handle[$k] = $str.'@'.$temp[1];
        }
    }
    return json_encode(array_filter($handle));
}

function spamcheck($field)
  {
  //filter_var() FILTER_SANITIZE_EMAIL 从字符串中删除电子邮件的非法字符
  //FILTER_VALIDATE_EMAIL 验证电子邮件地址
  $field=filter_var($field, FILTER_SANITIZE_EMAIL);
  if(filter_var($field, FILTER_VALIDATE_EMAIL))
    {
    return TRUE;
    }
  else
    {
    return FALSE;
    }
  }
function validReference(){
  $whiteList = [
    '127.0.0.1',
    '23.246.196.202',
    'localhost',
    'http://localhost',
    'www.winxdvd.com',
    'http://www.winxdvd.com',
    'www.macxdvd.com',
    'http://www.macxdvd.com',
    'www.5kplayer.com',
    'http://www.5kplayer.com',
    '23.246.239.119',
    '169.54.216.129',
  ];
  if(isset($_SERVER["HTTP_REFERER"]) && !empty($_SERVER["HTTP_REFERER"])){
    $url = parse_url($_SERVER["HTTP_REFERER"]);
    if(!in_array($url['host'],$whiteList))return false;else return true;
  }else{
    return false;
  }
}
?>
