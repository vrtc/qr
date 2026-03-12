<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "click_logs".
 *
 * @property int $id
 * @property int $link_id
 * @property string $ip
 * @property string|null $user_agent
 * @property string $created_at
 *
 * @property Link $link
 */
class ClickLog extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%click_logs}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['link_id', 'ip'], 'required'],
            [['link_id'], 'integer'],
            [['created_at'], 'safe'],
            [['ip'], 'string', 'max' => 45],
            [['user_agent'], 'string', 'max' => 255],
            [['link_id'], 'exist', 'skipOnError' => true, 'targetClass' => Link::class, 'targetAttribute' => ['link_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'link_id' => 'Link ID',
            'ip' => 'IP',
            'user_agent' => 'User Agent',
            'created_at' => 'Created At',
        ];
    }

    /**
     * Gets query for [[Link]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getLink()
    {
        return $this->hasOne(Link::class, ['id' => 'link_id']);
    }
}
