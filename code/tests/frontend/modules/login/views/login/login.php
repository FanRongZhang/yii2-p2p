<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use frontend\assets\LoginAsset;
    
    $this->title = '登录界面';
    LoginAsset::register($this);
?>

<!-- content product begin-->
<div class="main-cloum">
    <div class="login-mains">
         <div class="login-main-img">
            <img src="../../image/logobj.png">
        </div>

        <!-- <form> -->
        <?php $form = ActiveForm::begin([
            'id' => 'login-form',
            'options' => ['class' => ''],
        ]); ?>
            <div class="con-login-box">
                <div class="con-loginbox">
                    <div class="con-loginput1 clearfix">
                        <h1 class="fl con-tit-inputs">登录钱富宝</h1>
                        <div class="fr con-regist-inputs">没有账号？<a href="<?php echo Url::to(['/login/login/register']);?>">免费注册</a></div>
                    </div>
                    <div class="con-loginput2 clearfix">
                        <div class="con-input-mainst">
                            <input type="text" class="form-control mobile" placeholder="请输入已经注册的手机号码" name="UMember[mobile]" >
                        </div>
                    </div>
                    <div class="con-loginput2 clearfix">
                        <div class="con-input-mainst">
                            <input type="password" class="form-control password" placeholder="请输入密码" name="UMember[password]" >
                        </div>
                    </div>
                    <div class="con-loginput2 clearfix">
                        <div class="con-input-mainst form-group">
                            <input type="text" class="form-control loginborder" id="captcha" name="UMember[captcha]" size="6" maxlength="6" placeholder="验证码" limit="required:true" msg="验证码不能为空" title="请填写验证码" msgArea="try_info" style="width:150px;float:left;" />
                            <?php echo yii\captcha\Captcha::widget(['name'=>'verifyCode','captchaAction'=>'login/captcha','imageOptions'=>['id'=>'captchaimg', 'title'=>'换一个', 'alt'=>'换一个', 'style'=>'cursor:pointer;margin-left:10px;'],'template'=>'{image}']);?>
                        </div>
                    </div>
                    <div class="con-password"><a href="<?php echo Url::to(['/login/login/resetpassword']);?>">忘记密码？</a> </div>
                    <div class="mar6">
                        <button type="submit" class="login-btnter">登录</button>
                    </div>
                    <div class="con-error-prompt mar6">
                        <div class="con-errorbox"><i class="officicon"></i>错误提示信息统一在这里</div>
                    </div>
                </div>
            </div>
        <?php ActiveForm::end(); ?>
         <!-- </form> -->
    </div>
</div>
<!-- content product end-->

<?php $this->beginBlock('test'); ?>

    $(function(){
        $(".con-error-prompt").css('display', 'none');

        $(".login-btnter").click(function() {
            var ok1 = ok2 = false;
            //手机号验证
            var mobile = $.trim($('.mobile').val());
            if(mobile == ''){
                $(".con-error-prompt").show();
                $(".con-errorbox").text('手机号不能为空！');
                ok1 = false;
            }else{
                ok1 = true;
            }

            var password = $.trim($('.password').val());
            if(password == ''){
                $(".con-error-prompt").show();
                $(".con-errorbox").text('密码不能为空！');
                ok2 = false;
            }else{
                ok2 = true;
            }
            
            if(ok1 && ok2){
              $('form').submit();
            }else{
              return false;
            }
        });
    });

<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>