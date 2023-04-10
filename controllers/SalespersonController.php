<?php

namespace app\controllers;

use Yii;
use app\models\CardoenSalesPerson;
use app\models\Products;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * SalespersonController implements the CRUD actions for CardoenSalesPerson model.
 */
class SalespersonController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all CardoenSalesPerson models.
     * @return mixed
     */
    public function actionIndex()
    {
		$productId = $_GET['productId'];
		$productName = $_GET['productName'];
		
		$searchModel = new CardoenSalesPerson();

        $dataProvider = $searchModel->search(Yii::$app->request->get());
             return $this->render('index', [
                           'dataProvider' => $dataProvider,
                           'searchModel' => $searchModel,
						   'productId' => $productId,
						   'productName' => $productName,
         ]);

    }

    /**
     * Displays a single CardoenSalesPerson model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        $cardoenSalesPerson = CardoenSalesPerson::find()->where('Id=:Id',[':Id'=>$id])->one();
        $productId = $cardoenSalesPerson['productId'];
        $productName = $cardoenSalesPerson['dealerCode'];
        return $this->render('view', [
            'model' => $this->findModel($id),
            'productId'=>$productId,
            'productName'=>$productName

        ]);
    }

    /**
     * Creates a new CardoenSalesPerson model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new CardoenSalesPerson();
		$productId = $_GET['productId'];
        $productName = $_GET['productName'];		
        if ($model->load(Yii::$app->request->post())) {
			
			$productId = $_GET['productId'];
			$productName = $_GET['productName'];
			
			$query = new \yii\db\Query();
			$query->select(['max(Id) As Id']);
			$query->from('CardoenSalesPerson');
			$query->where(['productId' =>$productId]);
			$max = $query->one();
				
			if($max != null){			
				$maxId = $max['Id'];
				$maxId = $maxId +1;
			}
				$model->Id = $maxId;
				$model->productId = $productId;
				$model->dealerCode = $productName;
				$model->save();
            return $this->redirect(['index', 'id' => $model->Id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                'productId'=>$productId,
                'productName'=>$productName,
            ]);
        }
    }

    /**
     * Updates an existing CardoenSalesPerson model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $productId = $model->productId;
        $product = Products::find()->where('ProductId = :ProductId',[':ProductId'=>$productId])->one();
        $productName = $product['DealerCode'];
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->Id]);
        } else {
            return $this->render('update', [
                'model' => $model,'productId'=>$productId,'productName'=>$productName
            ]);
        }
    }

    /**
     * Deletes an existing CardoenSalesPerson model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the CardoenSalesPerson model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return CardoenSalesPerson the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CardoenSalesPerson::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
