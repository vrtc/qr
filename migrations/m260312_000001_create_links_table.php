<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%links}}`.
 * Has foreign keys to the tables `{{%click_logs}}`.
 */
class m260312_000001_create_links_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('{{%links}}', [
            'id' => $this->primaryKey(),
            'short_code' => $this->string(10)->notNull()->unique(),
            'original_url' => $this->string(2048)->notNull(),
            'click_count' => $this->integer()->defaultValue(0),
            'ip_last' => $this->string(45),
            'created_at' => $this->dateTime()->defaultValue(new \yii\db\Expression('CURRENT_TIMESTAMP')),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropTable('{{%links}}');
    }
}
