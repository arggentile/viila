<aside class="main-sidebar">

    <section class="sidebar">
       
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    [
                        'label' => 'Alumnos',
                        'icon' => 'users',
                        'url' => '#',
                        'items' => [
                            ['label' => 'Listado', 'icon' => 'arrow-right', 'url' => ['/alumno/listado'], 'active' => (strpos($this->context->route,  'alumno/listado')!==FALSE)?true:false,'visible' => Yii::$app->user->can('listarAlumnos')],
                            ['label' => 'Carga Alumno', 'icon' => 'arrow-right', 'url' => ['/alumno/empadronamiento'], 'active' => (strpos($this->context->route,  'alumno/empadronamiento')!==FALSE)?true:false, 'visible' => Yii::$app->user->can('cargarAlumno')],
                            ['label' => 'Familias', 'icon' => 'arrow-right', 'url' => ['/grupo-familiar/listado'], 'active' => (strpos($this->context->route,  'grupo-familiar')!==FALSE)?true:false, 'visible' => Yii::$app->user->can('listarFamilias')],        
                            ['label' => 'Egresar', 'icon' => 'arrow-right', 'url' => ['/alumno/egresar-alumnos'], 'active' => (strpos($this->context->route,  'alumno/egresar-alumnos')!==FALSE)?true:false,'visible' => Yii::$app->user->can('egresarAlumnos')],           
                        ],
                        'visible' => (Yii::$app->user->can('cargarAlumno') || Yii::$app->user->can('listarFamilias') || Yii::$app->user->can('listarAlumnos') || Yii::$app->user->can('egresarAlumnos')),
                    ],
                    
                        [
                            'label' => 'Usuarios',
                            'icon' => 'users',
                            'url' => ['/user/admin/index'],
                            //'visible' => Yii::$app->user->can('gestionUsuarios')
                        ],       
                    
                ],
            ]
        ) ?>

    </section>

</aside>
