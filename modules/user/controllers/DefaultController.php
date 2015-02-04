<?php

namespace frontend\modules\user\controllers;

use yii\web\Controller;
use common\models\User;
use common\models\Post;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

class DefaultController extends Controller
{
    public function actionIndex()
    {
        return $this->redirect(['show', 'username' => \Yii::$app->user->identity->username]);
    }

    /**
     * Shows user's profile.
     * @param  integer $username
     * @return \yii\web\Response
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionShow($username='')
    {
        $user = User::findOne(['username' => $username]);

        if ($user === null) {
            throw new NotFoundHttpException;
        }

        return $this->render('show', [
            'user' => $user,
            'dataProvider' =>$this->post($username),
        ]);
    }


    public function comment($username='')
    {
        # code...
    }

    public function post($username='')
    {
        return new ActiveDataProvider([
            'query' => Post::find(['username' => $username]),
        ]);
    }

    public function favorite($username='')
    {
        # code...
    }
}
