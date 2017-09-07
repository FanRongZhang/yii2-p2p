<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "qfb_notice".
 *
 * @property integer $id
 * @property string $title
 * @property string $summary
 * @property string $content
 * @property integer $is_send
 * @property integer $is_up
 * @property integer $create_time
 * @property integer $send_time
 * @property integer $show_end_time
 */
class QfbNotice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'qfb_notice';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['content','show_end_time','summary','title'], 'required'],
            [['content'], 'string'],
            [['is_send', 'is_up', 'create_time', 'send_time'], 'integer'],
            [['title'], 'string', 'max' => 150],
            [['summary'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => '公告标题',
            'summary' => '概要',
            'content' => '公告内容',
            'is_send' => '是否发送0否1是',
            'is_up' => '是否弹框0否1是',
            'create_time' => '创建时间',
            'send_time' => '公告发送时间',
            'show_end_time' => '公告显示截止时间',
        ];
    }
    /**获取公告列表
     * @author lwj
     */
    public function getNotice(){
        return QfbNotice::find()->select("id,title")->asArray()
            ->andWhere(['=','is_send',1])->orderBy(["create_time"=>SORT_DESC])->limit(5)
            ->all();
    }
    /**
     * @param int $id
     * @return bool|null|static
     */
    public function getOneById($id=0)
    {
        if (empty($id))
        {
            return false;
        }
        $new = QfbNotice::findOne($id);
        if ($new)
        {
            return $new;
        }
        else
        {
            return false;
        }

    }

    /**
     * @param $title
     * @param $content
     * @return string
     */
    public function setContent($title,$content)
    {
        $content = <<<END

<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <meta name="apple-mobile-web-app-capable" content="yes"/>
        <meta property="qc:admins" content="13666753740606375" />
        <meta property="wb:webmaster" content="02d1e0af77e62d70" />
        <title>$title</title>

    </head>
    <body >
    $content
    </body>
</html>
END;
        return $content;
    }


    public function getModel()
    {
        return new QfbNotice();
    }
}
