<?php

namespace app\models;

use Yii;
use \app\models\base\Lote as BaseLote;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "lote".
 */
class Lote extends BaseLote
{

    public $archivoentrante;
    public $cantregistros;
    
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
                ['cantregistros','safe'],
              [['archivoentrante'], 'file', 'skipOnEmpty' => true],
                 ['xfecha', 'date', 'format' => 'php:d-m-Y', 'message'=>'Ingrese una fecha valida'],
                ['fecha', 'date', 'format' => 'php:Y-m-d'],
            ]
        );
    }
    
     public function getXfecha()
    {
        if (!empty($this->fecha) && $valor = Fecha::convertirFecha($this->fecha,"Y-m-d","d-m-Y"))
        {
            
            return $valor;
        } else
        {
            return $this->fecha;
        }
    }

    public function setXfecha($value)
    {
        if (!empty($value) && $valor = Fecha::convertirFecha($value,"d-m-Y","Y-m-d"))
        {
            
            $this->fecha = $valor;
        } else
        {
            $this->fecha = $value;
        }
    }
    
    public function getCantidadregistros() {
        return count($this->registroLotes);
        
    }
}
