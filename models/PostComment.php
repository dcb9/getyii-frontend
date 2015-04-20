<?php

namespace common\models;

use frontend\modules\user\models\UserMeta;
use Yii;
use common\components\db\ActiveRecord;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "post_comment".
 *
 * @property integer $id
 * @property string $parent
 * @property string $post_id
 * @property string $comment
 * @property integer $status
 * @property string $user_id
 * @property string $like_count
 * @property string $ip
 * @property string $created_at
 * @property string $updated_at
 */
class PostComment extends ActiveRecord
{
    const TYPE = 'comment';
    /**
     * 发布
     */
    const STATUS_ACTIVE = 1;

    /**
     * 删除
     */
    const STATUS_DELETED = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'post_comment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent', 'post_id', 'status', 'user_id', 'like_count', 'created_at', 'updated_at'], 'integer'],
            [['post_id', 'comment', 'user_id', 'ip'], 'required'],
            [['comment'], 'string'],
            [['ip'], 'string', 'max' => 255]
        ];
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getPost()
    {
        return $this->hasOne(Post::className(), ['id' => 'post_id']);
    }

    public function getLike()
    {
        $model = new UserMeta();
        return $model->isUserAction(self::TYPE, 'like', $this->id);
    }

    /**
     * 通过ID获取指定评论
     * @param $id
     * @return array|null|\yii\db\ActiveRecord|static
     * @throws NotFoundHttpException
     */
    public static function findComment($id)
    {
        $model = static::find()
            ->where(['id' => $id, 'status' => self::STATUS_ACTIVE])
            ->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * 获取已经删除过的评论
     * @param $id
     * @return array|null|\yii\db\ActiveRecord
     * @throws NotFoundHttpException
     */
    public static function findDeletedComment($id)
    {
        $model = static::find()
            ->where(['id' => $id, 'status' => self::STATUS_DELETED])
            ->one();
        if ($model !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     *
     * @param $id
     * @return array|null|\yii\db\ActiveRecord
     * @throws NotFoundHttpException
     */
    public static function findCommentList($postId)
    {
        return static::find()->where(['post_id' => $postId, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * 自己写的评论
     * @return bool
     */
    public function isCurrent()
    {
        return $this->user_id == Yii::$app->user->id;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'         => 'ID',
            'parent'     => '父级评论',
            'post_id'    => '文章ID',
            'comment'    => '评论',
            'status'     => '1为正常 0为禁用',
            'user_id'    => '用户ID',
            'like_count' => '喜欢数',
            'ip'         => '评论者ip地址',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
        ];
    }
}
