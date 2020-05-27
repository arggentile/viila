<?php

use yii\db\Migration;

/**
 * Class m190906_105413_bonificacionesAlumnos
 */
class m190906_105413_bonificacionesAlumnos extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        echo "Aplicando migracion Bonificacion Alumno.\n";
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {            
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        
        //create table CategoriaBonificaciones
        $this->createTable('{{%categoria_bonificacion}}', [
            'id' => $this->primaryKey(),            
            'descripcion' => $this->string()->notNull(),
            'valor'=> $this->decimal(12, 2)->notNull(),
            'tipo_bonificacion' => $this->string(),
            'activa'=>  $this->binary()->notNull(),
        ], $tableOptions);    
        
        //create table BonificacionesFamiliares
        $this->createTable('{{%bonificacion_alumno}}', [
            'id' => $this->primaryKey(),
            'id_bonificacion' => $this->integer()->notNull(),
            'id_alumno'=> $this->integer()->notNull(),            
        ], $tableOptions);
        
        $this->execute('ALTER TABLE categoria_bonificacion MODIFY activa bit(1)'); 
        
       
        //ForeignKey Tabla BonificacionFamiliar
        $this->addForeignKey('fk_bonificacionAlumno_alumno', 'bonificacion_alumno', 'id_alumno', 'alumno', 'id');
        $this->addForeignKey('fk_bonificacionAlumno_bonificacion', 'bonificacion_alumno', 'id_bonificacion', 'categoria_bonificacion', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190906_105413_bonificacionesAlumnos cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190906_105413_bonificacionesAlumnos cannot be reverted.\n";

        return false;
    }
    */
}
