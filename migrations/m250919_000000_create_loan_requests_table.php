<?php

use yii\db\Migration;

class m250919_000000_create_loan_requests_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('loan_requests', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'amount' => $this->integer()->notNull(),
            'term' => $this->integer()->notNull(),
            'status' => $this->string(16)->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex('idx_loan_requests_user_id', 'loan_requests', 'user_id');
        $this->createIndex('idx_loan_requests_status', 'loan_requests', 'status');
    }

    public function safeDown()
    {
        $this->dropTable('loan_requests');
    }
}


