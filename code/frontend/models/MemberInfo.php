<?php
namespace frontend\models;

use common\models\QfbMemberInfo;
use trntv\filekit\behaviors\UploadBehavior;


class MemberInfo extends QfbMemberInfo
{

    public $thumbnail;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => UploadBehavior::className(),
                'attribute' => 'thumbnail',
                'pathAttribute' => 'avatar',
                'baseUrlAttribute' => false,
            ]
        ];
    }

    public function rules()
    {
        return [
            [['thumbnail'], 'required'],
            [['thumbnail'], 'safe']
        ];
    }

    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), ['thumbnail' => '头像地址']);
    }

}