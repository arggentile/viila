<?php

namespace app\models;

use Yii;
use \app\models\base\RegistroLote as BaseRegistroLote;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "registro_lote".
 */
class RegistroLote extends BaseRegistroLote
{

    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                 'bedezign\yii2\audit\AuditTrailBehavior'
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
