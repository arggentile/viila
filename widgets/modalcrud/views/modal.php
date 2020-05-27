    <?php
    yii\bootstrap\Modal::begin([        
        'id'=>'ModalCrudAjax',
        'header'=>$titulo,
        'class' =>'modal-scrollbar',
        'clientOptions'=> ['keyboard'=>false,'backdrop'=>'static'],
        'footer' => '<button type="submit" class="btn btn-default btn-submit-form">ACEPTAR</button>',
    ]);
        echo "<div id='modalContent'></div>";
    yii\bootstrap\Modal::end();
    ?>