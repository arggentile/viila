<?php

use yii\db\Migration;

/**
 * Class m190626_210851_addProfile_Usuarios
 */
class m200525_100851_addProfile_Usuarios extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('profile','apellido', $this->string());
        $this->addColumn('profile','nombre', $this->string());
        
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190626_210851_addProfile_Usuarios cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190626_210851_addProfile_Usuarios cannot be reverted.\n";

        return false;
    }
    */
}
