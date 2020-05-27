<?php

use yii\db\Migration;

/**
 * Class m190906_110536_descuentosServicioAlumno
 */
class m190906_110536_descuentosServicioAlumno extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {            
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->addColumn('persona', 'bautizado', $this->binary());        
        $this->addColumn('persona', 'lugar_bautismo', $this->string());
        
        $this->addColumn('persona', 'comunion', $this->binary());        
        $this->addColumn('persona', 'lugar_comunion', $this->string());
        
        $this->addColumn('persona', 'confirmacion', $this->binary());        
        $this->addColumn('persona', 'lugar_confirmacion', $this->string());
        
        $this->execute('ALTER TABLE persona MODIFY confirmacion bit(1)');
        $this->execute('ALTER TABLE persona MODIFY comunion bit(1)');
        $this->execute('ALTER TABLE persona MODIFY bautizado bit(1)');
        
        
        
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190906_110536_descuentosServicioAlumno cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190906_110536_descuentosServicioAlumno cannot be reverted.\n";

        return false;
    }
    */
}
