<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use frontend\assets\AppAsset;
    
    $this->title = '登录界面';
    AppAsset::register($this);
?>

<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide">
        <div class="regbox1">
            <div class="clearfix reg-titbox">
                <h1 class="fl">找回登录密码</h1>
                <div class="fr gray1 f18">已有账号，<a href="<?php echo Url::to(['/login/login/login']);?>" class="blue1">前去登录</a> </div>
            </div>
            <div class="clearfix reg-main">
                <div  class="retrieval-box">
                    <?php $form = ActiveForm::begin([
			            'id' => 'login-form',
			            'options' => ['class' => ''],
			        ]); ?>
                        <div class="reg-maints1">
                            <div class="clearfix">
                                <label class="fl reg-label">手机号码</label>
                                <div class="reginput-box fl">
                                    <div><input type="text" class="register-input mem-mobile" placeholder="请输入已注册的手机号码" name="UMember[mobile]"></div>
                                </div>
                            </div>
                            <div class="valid-text tel-vercode titners mobile"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                        </div>

                        <div class="reg-maints1">
                            <div class="clearfix">
                                <label class="fl reg-label">短信验证码</label>
                                <div class="reginput-box fl clearfix">
                                    <div class="form-groupts clearfix fl">
                                        <input type="text" class="register-input register-width mem-code" placeholder="请输入短信验证码" name="UMember[code]">
                                    </div>
                                    <button class="regyzdbtn coneget-btn fl" type="button">获取验证码</button>
                                </div>
                            </div>
                            <div class="valid-text tel-vercode titners code"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                        </div>

                        <div>
                            <div class="clearfix">
                                <label class="fl reg-label">重置登录密码</label>
                                <div class="reginput-box fl">
                                    <div><input type="password" class="register-input mem-pass" placeholder="请输入6~12位数字+字母组合" name="UMember[password]"></div>
                                </div>
                            </div>
                            <div class="valid-text tel-vercode titners password"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                        </div>
                        <div class="reg-maints1">
                            <div class="clearfix">
                                <label class="fl reg-label"></label>
                                <div class="regtiest fl mar15">
                                    <button type="submit" class="regbitntes">提交</button>
                                </div>
                            </div>
                        </div>
                    <?php ActiveForm::end(); ?>
                </div>

            </div>
        </div>
    </div>
</div>
<!-- content product end-->

<?php $this->beginBlock('test'); ?>

    $(function(){
        var ok1 = ok2 = ok3 = false;

        $(".mobile").css('display', 'none');
        $(".code").css('display', 'none');
        $(".password").css('display', 'none');

        $(".regbitntes").click(function() {
            var mobile = $('.mem-mobile').val();
            if(mobile == ''){
                $(".mobile").show();
                $(".mobile").text('手机号不能为空！');
                ok1 = false;
            }else if(!mobile.match(/^(((13[0-9]{1})|159|153)+\d{8})$/)){
                $(".mobile").show();
                $(".mobile").text('手机号码格式不正确！');
                ok1 = false;
            }else{
                $(".mobile").css('display', 'none');
                ok1 = true;
            }
            var code = $('.mem-code').val();
            if(code == ''){
                $(".code").show();
                $(".code").text('短信验证码不能为空！');
                ok2 = false;
            }else{
                $(".code").css('display', 'none');
                ok2 = true;
            }
            var password = $('.mem-pass').val();
            if(password == ''){
                $(".password").show();
                $(".password").text('密码不能为空！');
                ok3 = false;
            }else{
                $(".password").css('display', 'none');
                ok3 = true;
            }

            if(ok1 && ok2 && ok3){
              	$('form').submit();
            }else{
              	return false;
            }

        });
    });

<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>