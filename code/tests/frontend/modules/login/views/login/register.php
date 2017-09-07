<?php 

use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\ActiveForm;
use frontend\assets\AppAsset;
    
    $this->title = '注册界面';
    AppAsset::register($this);
?>

<!-- content product begin-->
<div class="main-cloum">
    <div class="q-wide">
        <div class="regbox1">
            <div class="clearfix reg-titbox">
                <h1 class="fl">注册</h1>
                <div class="fr gray1 f18">我已注册，<a href="<?php echo Url::to(['/login/login/login']);?>" class="blue1">现在登录</a> </div>
            </div>
            <div class="clearfix reg-main">
                <div class="fl">
                    <?php $form = ActiveForm::begin([
                        'id' => 'login-form',
                        'options' => ['class' => ''],
                    ]); ?>    
                        <div class="reg-maints1">
                            <div class="clearfix">
                                <label class="fl reg-label">用户类型</label>
                                <div class="fl regselect">
                                    <select class="member_type" name="UMember[member_type]">
                                        <!-- <option value ="">请选择</option> -->
                                        <option value="1">我是投资人</option>
                                        <option value ="2">我是借款人</option>
                                        
                                    </select>
                                </div>
                            </div>
                            <div class="valid-text tel-vercode titners membertype"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                        </div>

                        <div class="reg-maints1">
                            <div class="clearfix">
                                <label class="fl reg-label">手机号码</label>
                                <div class="reginput-box fl">
                                    <div><input type="text" class="register-input mem-mobile" placeholder="请输入未注册的手机号码" name="UMember[mobile]"></div>
                                </div>
                            </div>
                            <div class="valid-text tel-vercode titners mobile"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                        </div>

                        <div class="reg-maints1">
                            <div class="clearfix">
                                <label class="fl reg-label">图形验证码</label>
                                <div class="reginput-box fl">
                                    <div class="form-groupts">
                                        <input type="text" class="form-control loginborder" id="captcha" name="UMember[captcha]" size="6" maxlength="6" placeholder="验证码" limit="required:true" msg="验证码不能为空" title="请填写验证码" msgArea="try_info" style="width:150px;float:left;" />
                                        <?php echo yii\captcha\Captcha::widget(['name'=>'verifyCode','captchaAction'=>'login/captcha','imageOptions'=>['id'=>'captchaimg', 'title'=>'换一个', 'alt'=>'换一个', 'style'=>'cursor:pointer;margin-left:10px;'],'template'=>'{image}']);?>
                                    </div>
                                </div>
                            </div>
                            <div class="valid-text tel-vercode titners yanzm"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                        </div>

                        <div class="reg-maints1">
                            <div class="clearfix">
                                <label class="fl reg-label">短信验证码</label>
                                <div class="reginput-box fl clearfix">
                                    <div class="form-groupts clearfix fl">
                                        <input type="text" class="register-input register-width code" placeholder="请输入短信验证码" name="UMember[code]">
                                    </div>
                                    <button class="regyzdbtn coneget-btn fl" type="button">获取验证码</button>
                                </div>
                            </div>
                            <div class="valid-text tel-vercode titners duanxin"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                        </div>

                        <div>
                            <div class="clearfix">
                                <label class="fl reg-label">设置登录密码</label>
                                <div class="reginput-box fl">
                                    <div><input type="password" class="register-input mem-password" placeholder="请输入6~12位数字+字母组合" name="UMember[password]"></div>
                                </div>
                            </div>
                            <div class="valid-text tel-vercode titners password"><i class="officicon yzicont"></i>验证码有误，请重新输入</div>
                        </div>

                        <div class="clearfix mar6">
                            <label class="fl reg-label"></label>
                            <div class="regtiest fl">
                                <div class="smart-form td-pr-check">
                                    <label class="checkbox fl">
                                        <input type="checkbox" name="UMember[agreement]" checked="checked" id="CheckboxGroup1_0">
                                        <i></i>
                                    </label>
                                    <div class="damar1">我已阅读并同意<a href="<?php echo Url::to(['/login/login/xy']);?>" class="blue1">《注册服务协议》</a> </div>
                                </div>
                            </div>
                        </div>

                        <div class="reg-maints1">
                            <div class="clearfix">
                                <label class="fl reg-label"></label>
                                <div class="regtiest fl mar15">
                                    <button type="submit" class="regbitntes">马上注册</button>
                                </div>
                            </div>
                        </div>
                    <?php ActiveForm::end(); ?>
                </div>
                <div class="fr"><img src="../../image/regimg.png"></div>
            </div>
        </div>
    </div>
</div>
<!-- content product end-->

<?php $this->beginBlock('test'); ?>

    $(function(){
        var ok1 = ok2 = ok3 = ok4 = ok5 = false;

        $(".membertype").css('display', 'none');
        $(".mobile").css('display', 'none');
        $(".yanzm").css('display', 'none');
        $(".duanxin").css('display', 'none');
        $(".password").css('display', 'none');

        $(".regbitntes").click(function() {
            var type = $('.member_type').val();
            if(type == ''){
                $(".membertype").show();
                $(".membertype").text('用户类型必填！');
                ok1 = false;
            }else{
                $(".membertype").css('display', 'none');
                ok1 = true;
            }
            var mobile = $('.mem-mobile').val();
            if(mobile == ''){
                $(".mobile").show();
                $(".mobile").text('手机号不能为空！');
                ok2 = false;
            }else if(!mobile.match(/^(((13[0-9]{1})|159|153)+\d{8})$/)){
                $(".mobile").show();
                $(".mobile").text('手机号码格式不正确！');
                ok2 = false;
            }else{
                $(".mobile").css('display', 'none');
                ok2 = true;
            }
            var captcha = $('#captcha').val();
            if(captcha == ''){
                $(".yanzm").show();
                $(".yanzm").text('验证码不能为空！');
                ok3 = false;
            }else{
                $(".yanzm").css('display', 'none');
                ok3 = true;
            }
            var code = $('.code').val();
            if(code == ''){
                $(".duanxin").show();
                $(".duanxin").text('短信验证码不能为空！');
                ok4 = false;
            }else{
                $(".duanxin").css('display', 'none');
                ok4 = true;
            }
            var password = $('.mem-password').val();
            if(password == ''){
                $(".password").show();
                $(".password").text('密码不能为空！');
                ok5 = false;
            }else{
                $(".password").css('display', 'none');
                ok5 = true;
            }

            if(ok1 && ok2 && ok3 && ok4 && ok5){
              $('form').submit();
            }else{
              return false;
            }

        });
    });

<?php $this->endBlock()  ?>
<!-- 将数据块 注入到视图中的某个位置 -->
<?php $this->registerJs($this->blocks['test'], \yii\web\View::POS_END); ?>