<?php

use yii\db\Migration;

/**
 * Class m190906_105257_inicial
 */
class m200525_100852_createRolAdmin extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        echo "Aplicando migración Incial, tablas para configuraciones y llenado de datos estandrs\n";
        
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {            
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }       
        
        $auth = Yii::$app->authManager;
        
        $rolAdministrador = $auth->createRole('administrador');
        $auth->add($rolAdministrador);
        
        

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190906_105257_inicial cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190906_105257_inicial cannot be reverted.\n";

        return false;
    }
    */
}
