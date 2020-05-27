<?php

use yii\db\Migration;

/**
 * Class m190906_105257_inicial
 */
class m200525_100860_inicialConfig extends Migration
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

        
        /*******************************************************/
      
        $this->createTable('{{%lote}}', [
            'id' =>     $this->primaryKey(),
            'nombre' => $this->string(100)->notNull(),
            'fecha'=>  $this->date()->notNull()            
        ], $tableOptions);
        
        $this->createTable('{{%registro_lote}}', [
            'id' =>     $this->primaryKey(),            
            'id_lote' =>     $this->integer()->notNull(),
            'nombre_cliente' => $this->string(),
            'tipo_dni'=>$this->string(),
            'dni'=>$this->string(),
            'email'=>$this->string(),
            'concepto'=>  $this->string(),
            'monto'=>$this->decimal(10,2), 
            'error'=> $this->string()
        ], $tableOptions);
        
         $this->addForeignKey('fk_registrolote_lote', 'registro_lote', 'id_lote', 'lote', 'id');
         
         

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
