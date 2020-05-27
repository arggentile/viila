<?php

namespace app\models;

use Yii;
use \app\models\base\Tiket as BaseTiket;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tiket".
 */
class Tiket extends BaseTiket
{

    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                # custom behaviors
            ]
        );
    }

    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(),
            [
                # custom validation rules
            ]
        );
    }
}
