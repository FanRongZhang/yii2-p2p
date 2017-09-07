<?php
namespace api\common;

use yii\rest\ActiveController;
use yii\filters\ContentNegotiator;
use yii\web\Response;
use yii;
use api\common\SafeBehavior;
use api\common\AccessTokenBehavior;

/**
 * 资源列表型api控制器基类
 * @author xiaoma <xiaomalover@gmail.com>
 * @since 2.0
 */
class BaseActiveController extends ActiveController
{

    /**
     * 重写序列化方法
     */
    public $serializer = [
        'class' => 'api\common\Serializer',
        'collectionEnvelope' => 'data',
    ];

    /**
     * 初始化操作
     * @return void
     */
    public function init()
    {
        parent::init();
        Yii::$container->set('yii\data\Pagination',[
             'defaultPageSize'=>isset($_GET['limit']) ? $_GET['limit'] : 2
        ]);

    }

    /**
     * 封装行为
     * @return Array 所有行为的数组
     */
    public function behaviors()
    {
        //继承父类所有行为
        $behaviors = parent::behaviors();

        //安全验签行为
         $safe = [
             'class' => SafeBehavior::className(),
         ];

        //用户鉴权行为(access-token的验证)
        $behaviors['authenticator'] = [
            'class' => AccessTokenBehavior::className(),
        ];

        //内容格式协商行为
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                // 'application/xml' => Response::FORMAT_XML,
            ],
        ];

        //安全验签要放在最前面
        array_unshift($behaviors, $safe);

        return $behaviors;
    }

    /**
     * 把数据返回的格式改成我们所需要的格式
     * @param  Array $data 个性化之前的数据
     * @return Array $data 个性化之后的数据
     */
    protected function serializeData($data)
    {
        $data = Yii::createObject($this->serializer)->serialize($data);
        if (isset($data['page'])) {
            $total_page = $data['page']['pageCount'];
            $total_count = $data['page']['totalCount'];
            if ($total_count) {
                if (isset($_GET['page']) && $_GET['page'] > $total_page) {
                    $str = "没有更多数据";
                    $data['data'] = [];
                } else {
                    $str = "请求数据成功";
                }
            } else {
                $str = "没有数据";
                $data['data'] = [];
            }
            unset($data['links']);
            unset($data['page']);
        } else {
            if (isset($data['id'])){
                $str = "请求数据成功";
                $data['data'][] = $data;
            } else {
                $str = "没有数据";
                $data['data'] = [];
            }
        }
        return ['code'=>200,'msg'=>$str,'data'=>$data['data']];
    }
}
