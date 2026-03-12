<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%click_logs}}`.
 * Has foreign keys to the tables `{{%links}}`.
 */
class m260312_000002_create_click_logs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $this->createTable('{{%click_logs}}', [
            'id' => $this->primaryKey(),
            'link_id' => $this->integer()->notNull(),
            'ip' => $this->string(45)->notNull(),
            'user_agent' => $this->string(255),
            'created_at' => $this->dateTime()->defaultValue(new \yii\db\Expression('CURRENT_TIMESTAMP')),
        ]);

        // Creates index for column `link_id`
        $this->createIndex(
            '{{%idx-click_logs-link_id}}',
            '{{%click_logs}}',
            'link_id'
        );

        // Note: SQLite does not enforce foreign keys by default.
        // The relationship is handled at the application level (ClickLog::getLink).
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->dropIndex(
            '{{%idx-click_logs-link_id}}',
            '{{%click_logs}}'
        );

        $this->dropTable('{{%click_logs}}');
    }
}
