<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\modules\admin\models\Leads */

$this->title = $model->email;
$this->params['breadcrumbs'][] = ['label' => 'Leads', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="leads-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?//= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?//= Html::a('Delete', ['delete', 'id' => $model->id], [
            //'class' => 'btn btn-danger',
           // 'data' => [
              //  'confirm' => 'Are you sure you want to delete this item?',
              //  'method' => 'post',
           // ],
        //]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            // 'id',
            'email:email',
            'created',
            // 'modified',
        ],
    ]) ?>

</div>
