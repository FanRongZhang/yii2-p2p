<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>协议界面</title>
    <meta name="viewport" content="initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <style type="text/css">

        html,body,p{ margin:0; padding:0; font-family: "宋体"; font-size:12px; }
        body{background: #ebeef3;}
        .clearfix::after { height: 0px; clear: both; display: block; visibility: hidden; content: ""; }
        .clearfix { zoom: 1; }
        .xywarp{ max-width:960px; margin:0 auto;background: #fff; width: 100%;}
        .content{padding: 36px 20px;}
        .content p { line-height: 25px; font-size: 14px;padding:4px 0; }
    </style>
</head>
<body>
    <div class="xywarp">
        <div class="content">
            <p style="text-align: center; font-size: 22px; font-weight: bold;">借款协议</p>
            <br>
            <br>
            <p style="text-align: right;">协议编号：（<?php echo date('YmdHis', $touzi['create_time']); ?>）</p>
            <br>
            <br>
            <p>本协议由以下各方于 <?php echo date('Y', $touzi['create_time']);?> 年 <?php echo date('m', $touzi['create_time']);?> 月 <?php echo date('d', $touzi['create_time']);?> 日在深圳市 福田区 签订：</p>
            <br>
            <br>
            <p>甲方（投资人）：<?php echo $touziinfo['realname']; ?></p>
            <p>平台用户名：<?php echo $touziinfo['account']; ?></p>
            <p>身份证号码：<?php echo $touziinfo['card_no']; ?></p>
            <p>借款金额（人民币：元）:<?php echo $touzi['pay_money']; ?></p>
            <p>利率（%）：<?php echo $product['year_rate']; ?></p>
            <p>借款期限：<?php echo $product['invest_day']; ?>(天)</p>
            <br>
            <br>
            <p>乙方（借款人）: <?php echo $jiekuanren['realname']; ?></p>
            <p>平台用户名：<?php echo $jiekuanren['account']; ?></p>
            <p>身份证号码：<?php echo $jiekuanren['card_no']; ?></p>
            <p>地址：<?php echo $product['address']; ?></p>
            <p>联系电话：<?php echo $jiekuanren['mobile']; ?></p>
            <p>借款人专用账号：户名(<?php echo $memberbank['username']; ?>)、账号(<?php echo $memberbank['no']; ?>)、开户行(<?php echo $memberbank['name']; ?>)</p>
            <br>
            <br>
            <p>丙方（服务平台方）： 深圳国控股权投资基金管理有限公司</p>
            <p>营业执照号：91440300312028299Q</p>
            <p>法定代表人：金大鹏</p>
            <p>联系地址：福田区福田街道金田路皇岗商务中心2号楼20层</p>
            <br>
            <br>
            <br>
            <p style="font-size: 16px; "><b>鉴于：</b></p>
            <p style="text-indent: 25px;">1.丙方是一家在深圳市合法成立并有效存续的有限责任公司，拥有钱富宝Pro的经营权，提供金融信息咨询，为交易双方提供信息中介服务。负责在钱富宝Pro上发布乙方的融资标的信息；协助乙方完成其在钱富宝Pro上借款信息的发布，承诺保证乙方信息的真实性并协助甲方进行本协议项下的借款回收及贷后管理工作。</p>
            <p style="text-indent: 25px;">2.甲方、乙方均已自愿在钱富宝Pro注册，并承诺所提供信息真实、有效；所有行为均为其本人真实意愿。</p>
            <p style="text-indent: 25px;">3.甲方承诺对本协议涉及的借款具有完全的支配能力，是其自有闲散资金，为其合法所得；自愿将资金借给乙方有偿使用。</p>
            <p style="text-indent: 25px;">4.乙方有借款需求，自愿借入甲方资金有偿使用，且按照约定按时还款。甲乙双方形成借贷关系。</p>
            <p style="text-indent: 25px;">经各方协商一致，乙方经由丙方运营管理的钱富宝Pro向甲方借款、并由甲方承担逾期代偿责任或对甲方进行无条件债权收购等事宜，各方根据平等、自愿的原则特签订如下协议，共同遵照履行。</p>
            <br>
            <p style="font-size: 16px;text-indent: 25px; "><b>第一条 释义：</b></p>
            <p style="text-indent: 25px;">除非本协议另有规定，以下词语在本协议中定义如下：</p>
            <p style="text-indent: 25px;">1.1投资人（甲方）：指通过丙方钱富宝Pro成功注册为认证会员，自主选择出借一定数量资金给借款客户，且具有完全民事权利及行为能力的自然人。</p>
            <p style="text-indent: 25px;">1.2借款人（乙方）：指有一定的资金需求，经过丙方信用评估，在丙方钱富宝Pro注册为认证会员，由丙方推荐给投资人并得到投资人资金，且具有完全民事权利及行为能力的自然人。</p>
            <p style="text-indent: 25px;">1.3钱富宝Pro：指由丙方运营管理，专业提供借贷居间服务的网络借贷平台。</p>
            <p style="text-indent: 25px;">1.4资金账户：指本合同各当事人在钱富宝Pro注册认证成功后自动在第三方支付公司产生的专属虚拟账户，用于资金的划转、收付及其他操作。</p>
            <p style="text-indent: 25px;">1.5监管账户：以丙方名义在第三方支付机构或资金监管银行开立的、账户内资金独立于丙方其他资金的监管账户。</p>
            <br>
            <p style="font-size: 16px;text-indent: 25px; "><b>第二条 借款详情</b></p>
            <p style="text-indent: 25px;">乙方同意通过钱富宝Pro向甲方借款如下：</p>
            <br>
            <table border="1" style="margin: 0px auto; font-size:14px; border-collapse: collapse; border: 1px solid #000; width: 80%; ">
                <tbody>
                    <tr>
                        <td style="text-align: center; width: 20%; padding: 10px">借款本金数额</td>
                        <td style="padding: 10px">大写：<?php echo $jiekuanjine ?>（小写：<?php echo $product['stock_money']; ?>元）</td>
                    </tr>
                    <tr>
                        <td style="text-align: center; width: 20%;  padding: 10px">借款年化利率</td>
                        <td style="padding: 10px"> <?php echo $product['year_rate']; ?> %</td>
                    </tr>
                    <tr>
                        <td style="text-align: center; padding: 10px; width: 20%; ">借款期限</td>
                        <td style="padding: 10px; line-height: 25px;"> <?php echo intval($product['invest_day']/30); ?> 个月， <?php echo date('Y',$product['finish_time']); ?> 年 <?php echo date('m',$product['finish_time']); ?> 月 <?php echo date('d',$product['finish_time']); ?> 日起至 <?php echo date('Y',$endtime); ?> 年 <?php echo date('m',$endtime); ?> 月 <?php echo date('d',$endtime); ?> 日止（实际借款起始日期以甲方出借资金划入乙方资金账户之日为准，借款天数不变，借款截至日期随起始日期的变动而相应变动）</td>
                    </tr>
                    <tr>
                        <td style="text-align: center; width: 20%;  padding: 10px">借款详细用途</td>
                        <td style="padding: 10px"></td>
                    </tr>
                    <tr>
                        <td style="text-align: center; width: 20%;  padding: 10px">利息起算结算日</td>
                        <td style="padding: 10px;">以甲方出借资金划入乙方资金账户之日为利息起算日，借款期限届满日为利息结算日。</td>
                    </tr>
                </tbody>
            </table>
            <br>
            <br>
            <p style="font-size: 16px;text-indent: 25px; "><b>第三条 偿还方式</b></p>
            <p style="text-indent: 25px; ">3.1 本协议约定甲方依照以下第 
                <?php 
                    if($product['profit_type'] == 1){
                        echo 'c';
                    } elseif($product['profit_type'] == 2){
                        echo 'b';
                    } elseif($product['profit_type'] == 4){
                        echo 'a';
                    }
                ?>
             种方式还本付息：</p>
            <p style="text-indent: 25px; ">a．等额本息：即还款本息_____元/月；</p>
            <p style="text-indent: 25px; ">b．先息后本：即还款利息
            <?php
                if($product['profit_type'] == 2){
                    echo $huankuan[0]['interest'];
                }else{
                    echo "";
                }
                 
             ?>元/月，最后一期还款本金
                <?php 
                    if($product['profit_type'] == 2){
                        echo ($product['stock_money']+$huankuan[0]['interest']); 
                    }else{
                        echo "";
                    }
                ?>元；</p>
            <p style="text-indent: 25px; ">c．其他还款方式：到期还本付息。</p>
            <p style="text-indent: 25px; ">具体、确切的还款时间、金额见如下“还款计划表”。该“还款计划表”所示各期还款金额为乙方总还款额，乙方归还单个甲方投资人的金额为总还款额乘以单个甲方投资人的出借比例。乙方应按照“还款计划表”按时足额向甲方进行偿付。</p>
            <p style="text-indent: 25px; ">附： 还款计划表：</p>
            <br>
            <table border="1" style="margin: 0px auto; font-size:14px; border-collapse: collapse; border: 1px solid #000; width: 80%; ">
                <tbody>
                    <tr>
                        <td style="text-align: center; padding: 10px;">还款期数</td>
                        <td style="text-align: center; padding: 10px;">应还款日期</td>
                        <td style="text-align: center; padding: 10px;">应还本金</td>
                        <td style="text-align: center; padding: 10px;">应还利息</td>
                        <td style="text-align: center; padding: 10px;">应还总额</td>
                    </tr>
                    <?php foreach ($huankuan as $key => $value) : ?>
                        <tr>
                            <td style="padding: 10px;width:80px;text-align: center;"><?php echo intval($key+1); ?></td>
                            <td style="padding: 10px;text-align: center;">
                                <?php 
                                    if($value['periods'] == 1 && $value['is_end'] == 1){
                                        if($product['profit_day'] == 10){
                                            $profitDay = $product['finish_time']+$value['invest_day']*common\common\toolbox\Tool::$dayTime;
                                        }elseif($product['profit_day'] == 11){
                                            $profitDay = $product['finish_time']+($value['invest_day']+1)*common\toolbox\Tool::$dayTime;
                                        }elseif($product['profit_day'] == 20){
                                            $profitDay = $product['finish_time']+($value['invest_day'])*common\toolbox\Tool::$dayTime;
                                        }else{
                                            $profitDay = $product['finish_time']+($value['invest_day']+1)*common\toolbox\Tool::$dayTime;
                                        }
                                    }else{
                                        if($product['profit_day'] == 10){
                                            $profitDay = $product['finish_time']+common\toolbox\Tool::$dayTime*(($value['periods']-1)*common\toolbox\Tool::$periodsDay+$value['invest_day']);
                                        }elseif($product['profit_day'] == 11){
                                            $profitDay = $product['finish_time']+common\toolbox\Tool::$dayTime*(($value['periods']-1)*common\toolbox\Tool::$periodsDay+$value['invest_day']+1);
                                        }elseif($product['profit_day'] == 20){
                                            $profitDay = $product['finish_time']+common\toolbox\Tool::$dayTime*(($value['periods']-1)*common\toolbox\Tool::$periodsDay+$value['invest_day']);
                                        }else{
                                            $profitDay = $product['finish_time']+common\toolbox\Tool::$dayTime*(($value['periods']-1)*common\toolbox\Tool::$periodsDay+$value['invest_day']+1);
                                        }
                                    }
                                    echo $product['finish_time'] ? date('Y-m-d', $profitDay) : ''; 
                                ?>
                            </td>
                            <td style="padding: 10px;text-align: center;"><?php echo $value['money']; ?></td>
                            <td style="padding: 10px;text-align: center;"><?php echo $value['interest']; ?></td>
                            <td style="padding: 10px;text-align: center;"><?php echo $value['money']+$value['interest']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <td style="padding: 10px;" colspan="2">合计&nbsp;&nbsp;&nbsp;<?php echo $total+$ben; ?></td>
                        <td style="padding: 10px;text-align: center;"><?php echo $ben; ?></td>
                        <td style="padding: 10px;text-align: center;"><?php echo $total; ?></td>
                        <td style="padding: 10px;text-align: center;"><?php echo $total+$ben; ?></td>
                    </tr>
                </tbody>
            </table>
            <br>
            <p style="text-indent: 25px; ">3.2 乙方应于各还款日当日（不得迟于24:00）或之前将当期应还总金额充值于其资金账户，并不可撤销地授权丙方：委托合作的第三方支付机构或银行机构将其资金账户的还款金额划付至甲方资金账户，以甲方资金账户实际收到乙方的还款资金为准。</p>
            <p style="text-indent: 25px; ">3.3 如果还款日遇到法定假日或公休日，乙方应严格按照约定还款日期将相应还款资金充值于其资金账户并锁定。经甲方同意，丙方委托第三方支付机构或银行机构划付资金至甲方资金账户的日期相应顺延至节假日后的第一个工作日，乙方还款金额不变。</p>
            <p style="text-indent: 25px; ">3.4 还款日为债务到期日。</p>
            <p style="text-indent: 25px; ">3.5 如果实际借款起始日期早于或晚于本协议约定的借款起始日期X天，则“还款计划表”中的各期还款日期相应提前或延后X天。</p>
            <p style="text-indent: 25px; ">3.6 本协议如涉及乙方两人以上借款，任一借款人均应履行本协议项下的义务，对全部借款承担连带清偿责任，甲方有权向乙方任一借款人追索全部本金、利息、逾期罚息及其他费用。</p>
            <p style="text-indent: 25px; ">3.7乙方每期还款按照如下顺序清偿：（1）根据本协议产生的其他全部费用（2）逾期罚息（3）拖欠利息（4）拖欠本金（5）当期利息（6）当期本金</p>
            <p style="text-indent: 25px; ">3.8如乙方还款不足以偿还当期借款本金、利息和逾期罚息的，甲方同意各自按照其借出款项比例收取还款。</p>
            <br>
            <p style="font-size: 16px;text-indent: 25px; "><b>第四条 保证方式</b></p>
            <p style="text-indent: 25px; ">经各方协商，甲方选择 <?php echo $product['warranty_type']; ?> 方式对本合同项下借款进行保证。</p>
            <p style="text-indent: 25px; ">4.1 质押保证</p>
            <p style="text-indent: 25px; ">机动车质押作为乙方在丙方平台借款的保证，乙方将自己名下的汽车：</p>
            <p style="text-indent: 25px; ">牌照号为：<?php if($product['warranty_type'] == 1){echo $baozheng['plate_number'];}?>；&nbsp;&nbsp;型号为：<?php if($product['warranty_type'] == 1){echo $baozheng['model'];}?>；</p>
            <p style="text-indent: 25px; ">发动机号为：<?php if($product['warranty_type'] == 1){echo $baozheng['engine_number'];}?>；&nbsp;&nbsp;车架号为：<?php if($product['warranty_type'] == 1){echo $baozheng['vin'];}?>。</p>
            <p style="text-indent: 25px; ">向丙方作质押，约定该车辆于乙方收到甲方借款时，交由丙方占有。</p>
            <p style="text-indent: 25px; ">具体内容见“质押合同”合同编码为（<?php if($product['warranty_type'] == 1){echo $baozheng['contract_number'];}?>）。</p>
            <p style="text-indent: 25px; ">4.2 抵押担保</p>
            <p style="text-indent: 25px; ">抵押物的品名状况：</p>
            <p style="text-indent: 25px; ">抵押担保的范围：担保债权项下的本金、利息、违约金、损害赔偿金、实现抵押权的费用等。</p>
            <p style="text-indent: 25px; ">抵押权的实现：债务人逾期支付借款30天以上丙方有权依法折价、变卖、拍卖该抵押物，实现抵押权，所得资金用以清偿借款和实现债权、抵押权的费用（包括但不限于：案件受理费、财产保全费、评估鉴定费、拍卖费、执行费、律师代理费等）。</p>
            <p style="text-indent: 25px; ">具体内容见“抵押合同”合同编码为（<?php if($product['warranty_type'] == 2){echo $baozheng['contract_number'];}?>）。</p>
            <p style="text-indent: 25px; ">4.3 保证担保</p>
            <p style="text-indent: 25px; ">保证人：<?php if($product['warranty_type'] == 3){echo $baozheng['warrantor'];}?></p>
            <p style="text-indent: 25px; ">身份证号（或社会信用代码）：<?php if($product['warranty_type'] == 3){echo $baozheng['id_card'];}?></p>
            <p style="text-indent: 25px; ">联系电话：<?php if($product['warranty_type'] == 3){echo $baozheng['mobile'];}?> </p>
            <p style="text-indent: 25px; ">本合同的保证方式为：  <?php if($product['warranty_type'] == 3){echo $baozheng['guarantee_way'];}?>     （①一般保证；②连带责任保证）</p>
            <p style="text-indent: 25px; ">具体内容见“保证合同”合同编码为（<?php if($product['warranty_type'] == 3){echo $baozheng['contract_number'];}?>）。</p>
            <p style="font-size: 16px;text-indent: 25px; "><b>第五条 出借方式及流程</b></p>
            <p style="text-indent: 25px; ">5.1 本协议成立：乙方授权丙方在钱富宝Pro发布乙方借款需求，甲方按照钱富宝Pro的规则，通过认真阅读丙方在钱富宝Pro上发布的乙方借款需求信息及相关合同文件后，点击“投标”及投标“确认”按钮，完成投标操作，并且自动生成借款协议，本协议立即成立。</p>
            <p style="text-indent: 25px; ">5.2 出借资金冻结：甲方点击投标购买“确认”即视为其已经向丙方发出不可撤销的授权指令，授权丙方委托相应的第三方支付机构或资金监管银行等合作机构，冻结甲方资金账户中等同于其认购金额的资金。</p>
            <p style="text-indent: 25px; ">5.3 本协议生效：本协议在乙方发布的借款需求全部得到满足，且乙方借款需求所对应的资金已经全部冻结时立即生效。</p>
            <p style="text-indent: 25px; ">5.4 出借资金划转：本协议生效时，甲方即不可撤销地授权丙方委托合作的第三方支付机构或银行机构 ，将金额等同于其出借总额的资金，由甲方资金账户划转至乙方资金账户。乙方资金账户收到甲方出借资金，则视为借款发放成功，乙方应按照本协议约定承担还本付息的义务。</p>
            <p style="text-indent: 25px; ">5.5 出借资金的提现：乙方资金账户收到借款资金后，由乙方自行从资金账户提现至乙方注册时绑定的银行账户，完成提现操作。</p>
            <p style="text-indent: 25px; ">5.6出借资金的解冻：本协议未生效或失效时，甲方授权丙方委托合作的第三方支付机构或资金监管银行解冻冻结资金。</p>
            <p style="font-size: 16px;text-indent: 25px; "><b>第六条 提前还款</b></p>
            <p style="text-indent: 25px; ">6.1 乙方提出提前偿还全部借款，甲方授权丙方决定是否同意乙方提出的提前还款申请。若乙方提前还款申请得到允许，乙方仍需支付预期未完成的全部借款利息。</p>
            <p style="text-indent: 25px; ">6.2 乙方提出提早偿还部分借款，甲方授权丙方决定是否同意乙方提出的提前还款申请。若乙方提前部分还款申请得到允许，提前归还的部分借款可顺位冲抵后期还款。</p>
            <p style="text-indent: 25px; ">6.2乙方提前归还借款的，对于丙方已经收取的居间服务费、评审费、借款管理费等费用均不予退还。</p>
            <p style="font-size: 16px;text-indent: 25px; "><b>第七条 逾期还款</b></p>
            <p style="text-indent: 25px; ">7.1 如约定还款日24:00点前，乙方未能将应还资金足额充值于其资金账户，则视为逾期还款或部分逾期还款。</p>
            <p style="text-indent: 25px; ">7.2如乙方逾期还款且丙方未及时履行代偿责任的，乙方需按照当期应还本息，自逾期之日起按未还本息3‰/日的利率按日向甲方支付逾期罚息，直至清偿完毕之日止，逾期罚息不计复利。</p>
            <p style="text-indent: 25px; ">7.3 借款期间内，逾期罚息的计收标准可根据钱富宝Pro相关规则的变化进行相应调整。如相关规则发生变化，则钱富宝Pro会在网站公示该等规则的变化。</p>
            <p style="font-size: 16px;text-indent: 25px; "><b>第八条 收费及税费</b></p>
            <p style="text-indent: 25px; ">8.1 丙方有权就为本协议借款所提供的服务向乙方一次性收取平台居间服务费，居间服务费的收取以《居间服务协议》为准。</p>
            <p style="text-indent: 25px; ">8.2 甲方应自行负担并主动缴纳因利息收益带来的可能的税费。</p>
            <p style="text-indent: 25px; ">8.3  当甲方出借资金汇入乙方资金账户后，乙方同意并授权丙方委托合作的第三方支付机构或银行机构划付相关的一次性服务费用（包括咨询费、借款管理费，平台居间服务费等，所有服务费用内容以《居间服务协议》为准）至丙方资金账户。乙方、丙方对一次性服务费用的收取有其他特殊约定的，按照特殊约定操作。</p>
            <p style="font-size: 16px;text-indent: 25px; "><b>第九条 违约责任</b></p>
            <p style="text-indent: 25px; ">9.1 如果乙方擅自改变本协议规定的借款用途、严重违反本协议义务、提供虚假资料、故意隐瞒重要事实或未经甲方同意擅自转让本协议项下借款债务的视为乙方恶意违约，甲方有权提前终止本协议。甲方授权丙方判断乙方是否有上述恶意违约行为，并代为提出提前终止本协议的要求。乙方须在丙方提出终止本协议之日起3日内，按照本协议中约定的款项划转方式一次性向甲方支付余下的所有本金及截至实际还款日的利息、提前还款补偿金。构成犯罪的，甲方有权向相关国家机关报案，追究乙方刑事责任。</p>
            <p style="text-indent: 25px; ">9.2 发生下列任何一项或几项情形的，视为乙方严重违约：</p>
            <p style="text-indent: 45px; ">（1）乙方的重要财产遭受没收、征用、查封、扣押、冻结等可能影响其履约能力的不利事件，且不能及时提供有效补救措施的；</p>
            <p style="text-indent: 45px; ">（2）乙方的经营状况、财务状况出现影响其履约能力的不利变化，且不能及时提供有效补救措施的。</p>
            <p style="text-indent: 25px; ">9.3 若发生9.2款所述情形，或根据丙方合理判断乙方可能发生9.2款所述的违约事件的，丙方根据本合同特别条款规定采取下列任何一项或几项救济措施，乙方应无条件同意并积极配合丙方的行动：</p>
            <p style="text-indent: 45px; ">（1）立即暂缓、取消发放全部或部分借款；</p>
            <p style="text-indent: 45px; ">（2）宣布已发放借款全部提前到期，乙方应立即偿还所有应付款；</p>
            <p style="text-indent: 45px; ">（3）提前终止本协议，采取一切合法措施收回借款本息及违约金；</p>
            <p style="text-indent: 45px; ">（4）采取法律、法规以及本协议约定的其他救济措施。</p>
            <p style="text-indent: 25px; ">9.4 丙方根据本协议的规定代理甲方终止本协议的，如乙方在其资金账户下有任何余额，则乙方余额按照本协议第四条中相关约定进行提前清偿。</p>
            <p style="text-indent: 25px; ">9.5 甲方、丙方保留将乙方违约失信的相关信息纳入公民征信系统，保留其向媒体和乙方单位披露的权利。因乙方未按期还款而带来的调查及诉讼等费用由乙方承担。</p>
            <p style="font-size: 16px;text-indent: 25px; "><b>第十条 债权转让</b></p>
            <p style="text-indent: 25px; ">10.1 各方同意并确认，甲方可将本协议项下全部借款的债权转让予第三方，但该第三方必须为钱富宝Pro的注册认证用户。</p>
            <p style="text-indent: 25px; ">10.2 甲方根据本协议转让债权时，乙方不可撤销地授权丙方代为接收该等转让通知，丙方接到债权转让通知之时，该债权转让对乙方发生效力；债权受让人依法承接甲方在本协议项下的权利和义务。债权转让后，乙方需向债权受让人继续履行本协议下其对甲方的还款义务。</p>
            <p style="text-indent: 25px; ">10.3未经甲方、丙方事先书面（包括但不限于电子邮件等方式）同意，乙方不得将本协议项下的任何权利义务转让给任何第三方。</p>
            <p style="text-indent: 25px; ">10.4甲、乙、丙方一致同意，甲乙双方在本协议中预留的电话、地址通过发送短信、微信、信件以及甲乙双方在丙方平台上开立的账户等信息，在债权转让后，甲方或丙方以发送站内邮件（包括但不限于邮件）告知乙方债权转让的有关信息，即视为甲方已履行向乙方通知相关债权转让情形的义务，乙方不得以任何理由抗辩其未收到该通知。</p>
            <p style="font-size: 16px;text-indent: 25px; "><b>第十一条   特别约定</b></p>
            <p style="text-indent: 25px; ">11.1甲方不可撤销地授权丙方根据乙方的信用状况和经营状况自行决定提前收回乙方的借款本息，无需甲方另行书面授权。</p>
            <p style="text-indent: 25px; ">11.2乙方承诺，根据本协议中的规定，丙方代理甲方行使本协议项下的权利与义务的，乙方须无条件积极配合。</p>
            <p style="text-indent: 25px; ">11.3丙方对乙方的信用评估、风控调查、贷后管理和代理甲方的债权追偿，所采取的一切措施，乙方必须积极配合。</p>
            <p style="text-indent: 25px; ">11.4乙方不可撤销地授权丙方在钱富宝Pro上公布与乙方融资有关的企业及个人信息，无需乙方另行书面授权。</p>
            <p style="font-size: 16px;text-indent: 25px; "><b>第十二条   法律及争议解决</b></p>
            <p style="text-indent: 25px; ">本协议的签订、履行、终止、解释均适用中华人民共和国法律，本协议在履行过程中，如发生任何争议或纠纷，各方应友好协商解决；协商不能解决的，任何一方均可向合同签订地深圳市福田区人民法院提起诉讼。</p>
            <p style="font-size: 16px;text-indent: 25px; "><b>第十三条 其他</b></p>
            <p style="text-indent: 25px; ">13.1本协议采用电子文本形式制成，可以一份或者多份且具有同等法律效力，并永久保存在丙方为此设立的专用服务器上备查和保管，各方均认可该形式的协议效力。</p>
            <p style="text-indent: 25px; ">13.2本协议成立并生效后，在乙方将本协议项下的全部本金、利息、逾期罚息、管理费及其他相关所有费用清偿完毕后，本协议自动终止。</p>
            <p style="text-indent: 25px; ">13.3本协议签订之日起至借款全部清偿之日止，乙方或甲方的任何与本次借贷有关的信息发生变更时，应在三日内将更新后的信息提供给丙方，否则，由此带来的任何损失由该方自行承担。</p>
            <p style="text-indent: 25px; ">13.4 本协议的任何修改、补充均须以钱富宝Pro电子文本形式作出。</p>
            <p style="text-indent: 25px; ">13.5 各方均确认，本协议的签订、生效和履行以不违反法律为前提。如果本协议中的任何一条或多条违反适用的法律，则该条将被视为无效，但该无效条款并不影响本协议其他条款的效力。</p>
            <p style="text-indent: 25px; ">13.6 各方对本协议有未尽事项的，可以书面形式对本协议作出修改或补充，另订立补充协议，与本协议具有同等法律效力。</p>
            <p style="text-indent: 25px; ">（以下无正文）</p>
            <br>
            <br>
            <br>
            <p style="text-indent: 25px; ">协议签署各方：</p>
            <br>
            <p style="text-indent: 25px; ">甲方（签字盖章）：<?php echo $touziinfo['realname']; ?></p>
            <br>
            <p style="text-indent: 25px; ">乙方（签字盖章）：<?php echo $jiekuanren['realname']; ?></p>
            <br>
            <p style="text-indent: 25px; line-height:100px;width:300px;">丙方（签字盖章）：
                <img src="./gz.png" style="width:142px; float:right; height:142px;" />
            </p>
            
            <br>
            <p style="text-indent: 25px; ">授权代表签字：<?php echo $touziinfo['realname']; ?></p>
            <br>
            <p style="text-indent: 25px; ">签约时间：<?php echo date('Y', $touzi['create_time']); ?>年<?php echo date('m', $touzi['create_time']); ?>月<?php echo date('d', $touzi['create_time']); ?>日 </p>
            <br>
            <p style="text-indent: 25px; ">签约地点：<?php echo '深圳市福田区'; ?></p>
            
        </div>



    </div>
</body>
</html>