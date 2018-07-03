<?php
namespace app\index\controller;
use think\Controller;
use app\index\model\User;
use app\index\model\Newt;
use app\index\model\Newi;
use think\db;
use think\Request;
use think\Paginator;
class index extends Controller
{
    //主页面
 public function index(){
     $newt= new newt();
     $row=$newt->select();
     $this->assign('row',$row);
     $list = $newt->paginate(6);
     $page = $list->render();
// 模板变量赋值
     $this->assign('list', $list);
     $this->assign('page', $page);
// 渲染模板输出
     return $this->fetch();

 }
 //登陆功能
    public function login(){
        if(request()->isPost()){


            $name=input('post.name');
            $password=input('post.password');
            $cond=[];
            $cond['name|email']=$name;
            $cond['password']=md5(md5($password).'mdzz');
            $user= user::get($cond);
            if ($user){
                session('name',$name);

                return $this->success('登录成功','index');

            }
            return $this->success('登录失败');
        }
        return $this->fetch();
    }


    //注册功能
    public function register()
    {

        if (request()->isPost()) {
            $postData = input('post.');
            if (!captcha_check($postData['verify'])) {
                return $this->error('验证码校验失败!');
            }
            if (!$this->checkPassword($postData)) {
                return $this->error('密码校验失败!');
            }

            $user= new user();

            $row=$user->where('name',$postData['user'])->select();
            $email=$user->where('email',$postData['email'])->select();
            if (!empty($row[0])){
                return $this->success('用户名已被注册','register');
            }
            if (!empty($email[0])){
                return $this->success('邮箱已被注册','register');
            }
            $user->name=$postData['user'];
            $user->password=md5(md5($postData['password']).'mdzz');
            $user->email=$postData['email'];
            $user->save();
            return $this->success('注册成功','login');

        } else {
            return $this->fetch();
        }
    }



    //自定义对比密码方法
    private  function checkPassword($data){
     if (!$data['password']){
         return false;
     }
     if ($data['password']!==$data['password1']){
         return false;
     }
     return true;

    }
//登录用户退出
   public function logut(){
     session('name',null);
     
       return $this->success('退出成功','index');
   }

//发帖模块
   public  function ft()
   {
       if (request()->isPost()) {
           if (!captcha_check(input('verify'))) {
               return $this->error('验证码校验失败!','ft');
           }
           $txt = input('post.txt');
           $text = input('post.text');
           $newt= new newt();
           $newt->txt=$txt;
           $newt->text=$text;
           $newt->admin=session('name');
           $newt->save();
           return $this->success('发帖成功','index');


       }
       return $this->fetch();
   }

  //详情模块
    public function demo(){
        $id=input('id');
        $newt= new newt();
        $row=$newt->where('id',$id)->select();
        $this->assign('row',$row);
        $newi=new newi();
        $row1=$newi->where('newtid',$id)->select();
        $this->assign('row1',$row1);

        return $this->fetch();
    }
    //评论模块
  /*  public  function newl(){
        if(session('name')==null){
            return $this->success('请登录后发帖','login');

        }
        $id = input('id');
        $text = input('post.text');
        if (!$text=='') {

            $admin = session('name');
            $newi = new newi();
            $newi->newtid = $id;
            $newi->text = $text;
            $newi->admin = $admin;
            $newi->save();
            return $this->success('发帖成功', url('index/demo', ['id' => $id]));
        }
        return $this->success('不得为空', url('index/demo', ['id' => $id]));

    }*/
    public  function sousu(){
        $key=input('post.key');
        $newt= new newt();
        $row=$newt->where('txt','like',"%$key%")->select();
        $this->assign('roc',$row);
        return $this->fetch('index');


    }

    public  function ajax(){
        header("Content-type: text/html; charset=utf-8");
        if(session('name')==null){
           return "<script>alert('请登录后评论！');</script>";

        }
        if($_GET['pl']==null){
           return "<script>alert('不能为空！');</script>";

        }
        if(isset($_GET['pl'])){
          $text=$_GET['pl'];
          $id=$_GET['id'];
          $admin = session('name');
            $newi = new newi();
            $newi->newtid = $id;
            $newi->text = $text;
            $newi->admin = $admin;
            $newi->save();
            return "<h6>发布人$admin:$text</h6>";

     }
        return "<script>alert('不能为空！');</script>";


    }
}

