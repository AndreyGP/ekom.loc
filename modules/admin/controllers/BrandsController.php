<?php

namespace app\modules\admin\controllers;

use Yii;
use app\modules\admin\models\Brands;
use app\models\Product;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * BrandsController implements the CRUD actions for Brands model.
 */
class BrandsController extends Controller
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
     * Lists all Brands models.
     * @return mixed
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Brands::find(),
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'defaultOrder' => [
                    'title' => SORTTO
                ]
            ]
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Brands model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Brands model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Brands();

        if ($model->load(Yii::$app->request->post())) {
            $model->title = mb_strtolower($model->title);
            if ($model->save()){
                Yii::$app->cache->delete('brand_menu');
                Yii::$app->session->setFlash('success', 'Бренд <strong>"' . $model->title . '"</strong> добавлен.');
                return $this->redirect(['view', 'id' => $model->id]);
            }else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }

        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Brands model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->title = mb_strtolower($model->title);

        if ($model->load(Yii::$app->request->post())) {
            $model->title = mb_strtolower($model->title);
            if ($model->save()){
                Yii::$app->cache->delete('brand_menu');
                Yii::$app->session->setFlash('success', 'Бренд <strong>"' . $model->title . '"</strong> изменён.');
                return $this->redirect(['view', 'id' => $model->id]);
            }else {
                return $this->render('create', [
                    'model' => $model,
                ]);
            }
        } else {
            Yii::$app->session->setFlash('error', '<strong>Ошибка редактирования бренда </strong>');
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Brands model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        if (Product::find()->where(['brand_id' => $id])->count() >= 1){
            Yii::$app->session->setFlash('error', '<strong>Удаление невозможно!</strong> Бранд имеет вложенные товары');
            return $this->redirect(Yii::$app->request->referrer);
        }
        $this->findModel($id)->delete();
        Yii::$app->cache->delete('brand_menu');
        Yii::$app->session->setFlash('success', 'Бренд удален.');

        return $this->redirect(['index']);
    }

    /**
     * Finds the Brands model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Brands the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Brands::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
