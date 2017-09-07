<?php
namespace api\versions\v200\actions;
use Yii;
use yii\rest\Action;
use api\models\Product;
/**
 * @author xiaoma <xiaomalover@gmail.com>
 * @since 2.0
 */
class ProductViewAction extends Action
{

    /**
     * Displays a model.
     * @param string $id the primary key of the model.
     * @return \yii\db\ActiveRecordInterface the model being displayed
     */
    public function run($id)
    {
        // $model = $this->findModel($id);
        // if ($this->checkAccess) {
        //     call_user_func($this->checkAccess, $this->id, $model);
        // }
        $model = Product::find()
		->select("qfb_product.*,qfb_agreement.title")
		->joinWith('product_agreement')
		->where(['=','qfb_product.id',$id])
		->one();
        return $model;
    }

}