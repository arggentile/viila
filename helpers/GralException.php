<?php

namespace app\helpers;

class GralException extends \yii\base\UserException {
    
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        if($this->statusCode==null)
            return '';
        
    }

    
}