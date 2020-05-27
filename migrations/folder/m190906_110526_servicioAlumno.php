<?php

use yii\db\Migration;

/**
 * Class m190906_110526_servicioAlumno
 */
class m190906_110526_servicioAlumno extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        echo "Migración Tablas Servicios Alumno.\n";
        
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {            
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        $this->createTable('{{%servicio_alumno}}', [
            'id' =>         $this->primaryKey(),
            'id_servicio'=> $this->integer()->notNull(),
            'id_alumno' => $this->integer()->notNull(),        
            'fecha_otorgamiento' => $this->date()->notNull(),
            'fecha_cancelamiento' => $this->date(),
            'importe_servicio'=> $this->decimal(10,2)->notNull(),
            'importe_descuento' => $this->decimal(10,2)->notNull(),
            'importe_abonado'=> $this->decimal(10,2)->notNull(),            
            'id_estado'=>$this->integer()->notNull(),
        ], $tableOptions);    
        
        $this->addForeignKey('fk_servicioAlumno_servio', 'servicio_alumno', 'id_servicio', 'servicio_ofrecido', 'id');
        $this->addForeignKey('fk_servicioAlumno_alumno', 'servicio_alumno', 'id_alumno', 'alumno', 'id'); 
        $this->addForeignKey('fk_servicioAlumno_estado', 'servicio_alumno', 'id_estado', 'estado_servicio', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190906_110526_servicioAlumno cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190906_110526_servicioAlumno cannot be reverted.\n";

        return false;
    }
    */
}
