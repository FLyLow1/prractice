<?php
namespace frontend\controllers;

use yii;
use yii\web\Controller;
class ApiController extends Controller{
    public $enableCsrfValidation = false;

    /**
     * Notes: 获取天气的接口
     * User: renxingliang
     * Date: 2019/1/7
     * Time: 8:56
     */
    public function actionGetdata(){
        header("Content-type:text/html;charset=utf-8");
        $city=yii::$app->request->get('city');
        $remote_addr=$_SERVER['REMOTE_ADDR'];
        $server_addr=$_SERVER['SERVER_ADDR'];
        //记录调用接口的用户信息
        yii::$app->db->createCommand()->insert('logs',[
                'remote_addr'=>$remote_addr,
                'server_addr'=>$server_addr,
                'city'=>$city
        ])->execute();
        //判断城市是否为空,根据情况不同作出不同处理
        if(empty($city)){
            $data=[
                'msg'=>1,
                'msgmessage'=>'城市未选取',
            ];
            echo json_encode($data,JSON_UNESCAPED_UNICODE);
        }else{
            $sql="select * from `weather` WHERE citynm='$city'";
            $result=yii::$app->db->createCommand($sql)->queryAll();
            if($result && time()-filemtime("$city".'txt') <= 300){
                echo json_encode($result);
            }else{
                $url="http://api.k780.com:88/?app=weather.future&weaid=".$city."&appkey=10003&sign=b59bc3ef6191eb9f747dd4e83c99f2a4&format=json";
                $data=file_get_contents($url);
                $res=json_decode($data)->result;
                foreach ($res as $k=>$v){
                    $days=$v->days;
                    $week=$v->week;
                    $citynm=$v->citynm;
                    $temperature=$v->temperature;
                    $weather=$v->weather;
                    $weather_icon=$v->weather_icon;
                    $weather_icon1=$v->weather_icon1;
                    $wind=$v->wind;
                    $winp=$v->winp;
                    $res=yii::$app->db->createCommand()->insert('weather',[
                        'days'=>$days,
                        'week'=>$week,
                        'citynm'=>$citynm,
                        'temperature'=>$temperature,
                        'weather'=>$weather,
                        'weather_icon'=>$weather_icon,
                        'weather_icon1'=>$weather_icon1,
                        'wind'=>$wind,
                        'winp'=>$winp
                    ])->execute();
                }
                file_put_contents("$city".'txt','1');
                echo $res;
            }
        }
    }

    /**
     * Notes: curl方法执行get,post,delete,put请求
     * User: renxingliang
     * Date: 2019/1/8
     * Time: 10:31
     * @param $url 要访问的网址
     * @param $data 要传输的数据
     * @param string $method 访问的method方法
     * @return mixed  返回的结果
     */
    public function curlRequest($url,$data,$method='post'){
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,1);
        curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$method);
        $data=curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * Notes: 登录接口
     * User: renxingliang
     * Date: 2019/1/10
     * Time: 13:53
     * @throws yii\db\Exception
     */
     public  function actionTlogin(){
         $user=yii::$app->request->post('user');
         $pwd=md5(yii::$app->request->post('pwd'));
         if(empty($user) || empty($pwd)){
             $data=[
                 'msg'=>1000,
                 'msgmessage'=>"用户名和密码不能为空"
             ];
         }else{
             //根据用户名和密码查询用户的id
             $sql="select * from `exam_user` WHERE user='$user' AND pwd='$pwd'";
             $result=yii::$app->db->createCommand($sql)->queryOne();
             if($result){
                 $uid=$result['uid'];
                 $sql="select * from `exam_token` WHERE uid='$uid'";
                 $res=yii::$app->db->createCommand($sql)->queryOne();
                 if($res){
                     //执行到此处 说明该用户已经生成token
                    //判断token是否过期
                     $time=$res['create_time'];
                     if(time() - $time >=300){
                         $time=time();
                         $str=$user.$pwd.$time;
                         $token=sha1($str);
                         //生成新的token进行更新
                         yii::$app->db->createCommand()->update('exam_token',['token'=>$token,'create_time'=>time()],"uid='$uid'")->execute();
                         $data=[
                             'msg'=>1005,
                             'message'=>'token is too old ,create new token',
                             'data'=>[
                                 'token'=>$token,
                                 'time'=>$time
                             ]
                         ];
                     }else{
                         //time没过期 直接返回
                         $data=[
                             'msg'=>1005,
                             'message'=>'token still new',
                             'data'=>[
                                 'token'=>$res['token'],
                                 'time'=>$time
                             ]
                         ];

                     }
                 }else{
                     //执行到此处 说明该用户第一次生成token
                     //生成token
                     $time=time();
                     $str=$user.$pwd.$time;
                     $token=sha1($str);
                     //将token和 用户id 创建时间存入数据库
                     $res=yii::$app->db->createCommand()->insert('exam_token',[
                         'token'=>$token,
                         'create_time'=>$time,
                         'uid'=>$uid
                     ])->execute();
                     $data=[
                         'msg'=>0,
                         'msgmessage'=>'create token success',
                         'data'=>[
                             'token'=>$token,
                             'time'=>$time
                         ]
                     ];
                 }
             }else{
                 $data=[
                     'msg'=>1001,
                     'msgmessage'=>'用户名或者密码错误',
                 ];
             }
         }
        echo json_encode($data);
     }
    public function actionTest(){
         $token=yii::$app->request->get('token');
         if(empty($token)){
             $data=[
                 'msg'=>1,
                 'msgmessage'=>'token不能为空'
             ];
         }else{
             //验证token是否存在
             $sql="select * from  `exam_token` WHERE token='$token'";
             $res=yii::$app->db->createCommand($sql)->queryOne();
             if($res){
                 $data=[
                     'msg'=>1002,
                     'msgmessage'=>'查无此token'
                 ];
             }else{
             $old_time=$res['create_time'];
             if(time()-$old_time >= 300){
                 $data=[
                     'msg'=>1003,
                     'msgmessage'=>'token已过期'
                 ];
             }
             }
         }
        echo json_encode($data);
    }
    public function actionAdduser(){
        $name=yii::$app->request->get('name');
        $pwd=yii::$app->request->get('pwd');
        $gender=yii::$app->request->get('gender');
        $email=yii::$app->request->get('email');
        $phone=yii::$app->request->get('phone');
        $res=yii::$app->db->createCommand()->insert('test_user',[
            'name'=>$name,
            'pwd'=>$pwd,
            'gender'=>$gender,
            'email'=>$email,
            'phone'=>$phone
        ])->execute();
        $data=[
            'msg'=>1007,
            'messag'=>'success'
        ];
        echo json_encode($data);
    }
    public  function actionGetuser(){
         $sql="select * from `test_user`";
         $data=yii::$app->db->createCommand($sql)->queryOne();
        $data=[
            'msg'=>1009,
            'data'=>$data
        ];
         echo json_encode($data);
    }

    //day 15 查询抽奖人员名单
    public  function actionGetperson(){
         $sql="select * from  `lucky_person` WHERE status='0'";
         $data=yii::$app->db->createCommand($sql)->queryAll();
         $num=count($data);
         $data=[
             'msg'=>1,
             'data'=>$data,
             'num'=>$num
         ];
         echo json_encode($data);
    }
    public  function actionGetluckyperson(){
        $sql="select * from  `lucky_person` WHERE status='1'";
        $data=yii::$app->db->createCommand($sql)->queryAll();
        $num=count($data);
        $data=[
            'msg'=>1,
            'data'=>$data,
            'num'=>$num
        ];
        echo json_encode($data);
    }
    public function actionTery(){
        $sql="select * from  `lucky_person` WHERE status='0'";
        $data=yii::$app->db->createCommand($sql)->queryAll();
        $lucky_person=$data[array_rand($data)];
        $name=$lucky_person['name'];
        $sql="update `lucky_person` set status=1 WHERE name='$name'";
        $res=yii::$app->db->createCommand($sql)->execute();
        echo json_encode($lucky_person);
    }

    /**
     * Notes: 封装curl调用接口
     * User: renxingliang
     * Date: 2019/1/14
     * Time: 14:32
     * @param $url 接口地址
     * @param string $method 请求方式
     * @param string $data 发送的数据
     */
    public function curl($url,$method='get', $data=null){
//        echo $url.$method.$data;die;
         $ch=curl_init();
         curl_setopt($ch,CURLOPT_URL,$url);
         curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
         if($method!='get'){
           curl_setopt($ch,CURLOPT_CUSTOMREQUEST,$method);
           curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
           curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
         }
         $data=curl_exec($ch);
         curl_close($ch);
         return $data;
    }

    /**
     * Notes: 测试get传值
     * User: renxingliang
     * Date: 2019/1/14
     * Time: 14:41
     */
    public function actionGettest(){
        $data=[
            'msg'=>1,
            'message'=>'seccess'
        ];
        echo json_encode($data);
    }
    public function actionPost(){
        $name=yii::$app->request->post('name');
        $sex=yii::$app->request->post('sex');
        $data=[
            'name'=>$name,
            'sex'=>$sex
        ];
        $res=[
            'msg'=>101,
            'message'=>'success',
            'data'=>$data
        ];
        echo json_encode($res);
    }
    public function actionTest1(){
        header("Content-type:text/html;charset=utf-8");
//        $url="http://www.xznn.xyz/week3_nine/advanced/frontend/web/index.php?r=api/ti";
//        $url="http://120.79.16.97/advanced/frontend/web/index.php?r=api/getest";
        $data=[
            'id'=>'2',
            'name'=>'王三胖'
        ];
//        $res='';
//        foreach ($data as $k => $v){
//           $res.=$k."=".$v."&";
//        }
//        $res=rtrim($res,'&');
        $res=urldecode(http_build_query($data));
        echo $res;die;
//        $data=$this->curl($url,'PUT',$res);
//        echo $data;
    }
    public  function actionAddtestuser(){
        for ($i=1;$i<=1000;$i++){
            $name=rand(1,999999);
            $gender=rand(0,1);
            $age=rand(20,35);
            $birthday=rand(1,12);
            if($birthday>=10){
                $birthday=$birthday.rand(1,30);
            }else{
                $birthday="0".$birthday.rand(1,30);
            }
            $ebdata=['小学','初中','高中','专科','本科','研究生'];
            $e=rand(0,5);
            $edback=$ebdata[$e];
            $expensive=rand(0,10)."年";
            $sql="insert into `test17_user`(name,gender,age,birthday,edback,expensive) VALUES ('$name','$gender','$age','$birthday','$edback','$expensive')";
            $res=yii::$app->db->createCommand($sql)->execute();
            if($res){
                echo $sql."<br/>";
            }
        }
    }
    //month test start ------------------10移动项目实战月考技能---B卷----------------------------------

    /**
     * Notes: 登录接口
     * User: renxingliang
     * Date: 2019/1/16
     * Time: 10:48
     * @throws yii\db\Exception
     */
    public function actionMlogin(){
        parse_str(file_get_contents("php://input"),$data);
        $name=$data['name'];
        $pwd=md5($data['pwd']);
        $sql="select * from `gd_user` WHERE name='$name' AND pwd='$pwd'";
        $res=yii::$app->db->createCommand($sql)->execute();
        if($res){
            //验证用户名密码都正确
            if(empty($res['token'])){
                //说明用户为首次登录 ,为用户生成token,并进行token入库和时间更新
                $token=$this->createToken($name,$pwd);
                file_put_contents('$name.txt',$token);
                yii::$app->db->createCommand()->update('gd_user',[
                    'logintime'=>time(),
                    'status'=>1
                ],"id={$res['id']}")->execute();
                $reply=[
                    'msg'=>2,
                    'message'=>'success',
                    'token'=>$token
                ];
            }else{

                //说明用户登录过,验证token是否过期,如果过期提示相应信息并为用户生成新的有效token,没过期则登录成功.

            }
        }else{
            //用户名和密码有误 给用户返回信息

            $reply=[
                'msg'=>1,
                'message'=>'用户名或密码有误,请重新验证'
            ];
        }

    }
    public function createToken($name,$pwd){
        $token=$name.$pwd.time();
        $token=sha1(md5(urlencode($token)));
        return $token;
    }
    public function actionTtt(){
        //事务demo
        $a=yii::$app->db->beginTransaction();
            $sql1="update `test_user` set pwd='123456' WHERE id=13";
        $res1 =   $a->db->createCommand($sql1)->execute();
//        $res1 = yii::$app->db->createCommand($sql1)->execute();
        $sql2="update `test_user` set pwd='123456' WHERE id=2";
        $res2 =  $a->db->createCommand($sql2)->execute();
            if($res1 && $res2){
                $a->commit();
            }else{
                $a->rollBack();
            }

    }














}
