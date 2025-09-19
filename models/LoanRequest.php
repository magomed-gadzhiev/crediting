<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * Class LoanRequest
 *
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property int $term
 * @property string $status
 * @property int $created_at
 * @property int $updated_at
 */
class LoanRequest extends ActiveRecord
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_DECLINED = 'declined';

    public static function tableName(): string
    {
        return 'loan_requests';
    }

    public function rules(): array
    {
        return [
            [['user_id', 'amount', 'term'], 'required'],
            [['user_id', 'amount', 'term', 'created_at', 'updated_at'], 'integer'],
            [['status'], 'string', 'max' => 16],
        ];
    }
}


