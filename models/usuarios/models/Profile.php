<?php

namespace app\models\usuarios\models;

use Yii;
use Da\User\Model\Profile as BaseProfile;
use Da\User\Model\User as BaseUser;

class Profile extends BaseProfile {
   
    /**
     * Override from parent
     */
    public function rules() {
       
        return \yii\helpers\ArrayHelper::merge(
            parent::rules(),
            [
                ['apellido', 'string', 'max' => 255],
                ['nombre', 'string', 'max' => 255],
            ]
        );
    }
    
   
}