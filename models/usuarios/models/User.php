<?php

namespace app\models\usuarios\models;

use Yii;

use Da\User\Model\User as BaseUser;

class User extends BaseUser {

  
  
    public function can(){
       return true;
    } 
   
   public function getMisRoles(){
       $roles = Yii::$app->authManager->getRolesByUser(Yii::$app->user->getId());
       return $roles; 
   }
    
   
}