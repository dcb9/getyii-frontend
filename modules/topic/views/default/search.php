<?php

use yii\helpers\Html;
use yii\widgets\ListView;
use yii\widgets\Pjax;
use frontend\widgets\TopicSidebar;

use kartik\icons\Icon;

Icon::map($this);

$this->title = '社区';
$keyword = Yii::$app->request->getQueryParam('keyword');
if ($node = Yii::$app->request->getQueryParam('node')) {
    $node = \common\models\PostMeta::find()->where(['alias' => $node])->one();
}

?>
<div class="col-md-10 topic">
    <div class="panel panel-default">
        <?php if ($node): ?>
            <div class="panel-heading clearfix">
                <?= Icon::show('cloud-upload') ?> <?= $node->name; ?>
                <?php if (!empty($node->description)): ?>
                    <br/>
                    <span style="color: #666666; font-size: 12px;"><?= $node->description; ?></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="panel-heading clearfix">
            <div class="pull-left">搜索：<?= $keyword; ?>
            </div>
        </div>

        <?php Pjax::begin(['scrollTo' => 0]); ?>
        <?= ListView::widget([
            'dataProvider' => $dataProvider,
            'itemOptions' => ['class' => 'list-group-item'],
            'summary' => false,
            'itemView' => '_search',
            'options' => ['class' => 'list-group'],
        ]) ?>
        <?php Pjax::end(); ?>

    </div>
    <?= \frontend\widgets\Node::widget(); ?>
</div>
<?= TopicSidebar::widget([
    'node' => $node
]); ?>

