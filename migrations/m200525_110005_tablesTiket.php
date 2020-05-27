<?php

use yii\db\Migration;

/**
 * Class m200411_195259_tablesTikets
 */
class m200525_110005_tablesTiket extends Migration
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
        
        //create table Categoria Servicios Ofrecidos
        $this->createTable('{{%tiket}}', [
            'id' => $this->primaryKey(),
            'nro_tiket' => $this->string(50),
            'fecha_tiket' => $this->date()->notNull(),
            'importe'=>$this->decimal(10,2)->notNull(),
            'fecha_pago' => $this->date()->notNull(), 
            'detalles'=>$this->string(),
            'id_registro'=>$this->integer()->notNull(),
        ], $tableOptions);
        
         
        //create table formas_pago
        $this->createTable('{{%factura}}', [
            'id' =>     $this->primaryKey(),
            'fecha_factura' => $this->date()->notNull(),
            'nroFactura'=>  $this->string()->notNull(),   
            'informada'=>$this->string(1)->notNull(),
            'fecha_informada'=>$this->date(),
            'monto'=>$this->decimal(12,2)->notNull(),
            'cae'=>$this->string(),
            'ptoVta'=>$this->string(),
            'id_tiket'=> $this->integer(),
            'errores'=>$this->text()
        ], $tableOptions);
        
        $this->addForeignKey('fk_tiket_registro', 'tiket', 'id_registro', 'registro_lote', 'id');
        $this->addForeignKey('fk_factura_tiket', 'factura', 'id_tiket', 'tiket', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200411_195259_tablesTikets cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200411_195259_tablesTikets cannot be reverted.\n";

        return false;
    }
    */
}
