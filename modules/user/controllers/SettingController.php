<?php

namespace frontend\modules\user\controllers;

use Yii;
use common\models\User;
use frontend\modules\user\models\AccountForm;
use common\models\UserInfo;
use yii\filters\AccessControl;
use frontend\modules\user\models\UserAccount;
use yii\data\ActiveDataProvider;
use common\components\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\widgets\ActiveForm;

/**
 * SettingController implements the CRUD actions for User model.
 */
class SettingController extends Controller
{
    /** @inheritdoc */
    public $defaultAction = 'profile';

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'disconnect' => ['post']
                ],
            ],
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow'   => true,
                        'actions' => ['profile', 'account', 'confirm', 'networks', 'connect', 'disconnect'],
                        'roles'   => ['@']
                    ],
                ]
            ],
        ];
    }

    /** @inheritdoc */
    public function actions()
    {
        return [
            'connect' => [
                'class'           => 'yii\authclient\AuthAction',
                'successCallback' => [$this, 'connect'],
            ]
        ];
    }

    /**
     * 修改个人资料
     * @return mixed
     */
    public function actionProfile()
    {
        $model = UserInfo::findOne(['user_id'=>Yii::$app->user->id]);
        if ($model->load(Yii::$app->request->post()) && $model->save()) {

            $this->flash('更新成功', 'success');
            return $this->refresh();
        }

        return $this->render('profile', [
            'model' => $model,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     */
    public function actionAccount()
    {
        /** @var SettingsForm $model */
        $model = Yii::createObject(AccountForm::className());

        $this->performAjaxValidation($model);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', '您的用户信息修改成功');
            return $this->refresh();
        }

        return $this->render('account', [
            'model' => $model,
        ]);
    }


    /**
     *  第三方账号绑定
     * @return mixed
     */
    public function actionNetworks()
    {
        return $this->render('networks', [
            'user' => Yii::$app->user->identity
        ]);
    }


    /**
     * Connects social account to user.
     * @param  ClientInterface $client
     * @return \yii\web\Response
     */
    public function connect(ClientInterface $client)
    {
        $attributes = $client->getUserAttributes();
        $provider   = $client->getId();
        $clientId   = $attributes['id'];

        $account = $this->finder->findAccountByProviderAndClientId($provider, $clientId);

        if ($account === null) {
            $account = Yii::createObject([
                'class'     => UserAccount::className(),
                'provider'  => $provider,
                'client_id' => $clientId,
                'data'      => json_encode($attributes),
                'user_id'   => Yii::$app->user->id,
                'created_at' => time(),
            ]);
            $account->save(false);
            Yii::$app->session->setFlash('success', '账号绑定成功');
        } else {
            Yii::$app->session->setFlash('error', '绑定失败，此账号已经绑定过了');
        }

        $this->action->successUrl = Url::to(['/user/setting/networks']);
    }

    /**
     * Performs ajax validation.
     * @param Model $model
     * @throws \yii\base\ExitException
     */
    protected function performAjaxValidation($model)
    {
        if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post())) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            echo json_encode(ActiveForm::validate($model));
            Yii::$app->end();
        }
    }
}
