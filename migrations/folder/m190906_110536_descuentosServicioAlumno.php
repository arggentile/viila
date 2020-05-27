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
        
        //create table BonificacionesFamiliares
        $this->createTable('{{%bonificacion_servicio_alumno}}', [
            'id' => $this->primaryKey(),            
            'id_bonificacion' => $this->integer()->notNull(),
            'id_servicioalumno'=> $this->integer()->notNull(),            
        ], $tableOptions); 
        
        // Foreign Key
        $this->addForeignKey('fk_bonificacionSerivioAlumno_servicioAlumno', 'bonificacion_servicio_alumno', 'id_servicioalumno', 'servicio_alumno', 'id');
        $this->addForeignKey('fk_bonificacionSerivioAlumno_bonificacion', 'bonificacion_servicio_alumno', 'id_bonificacion', 'categoria_bonificacion', 'id');

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
