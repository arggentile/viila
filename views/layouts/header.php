<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
?>

<header class="main-header">

    <?= Html::a('SiCaP', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">
            
                <li>
                    <a class="" href="javascript:void(0);" onclick="javascript:ayuda();"><i class="fa fa-life-bouy"></i> Nesecito Ayuda?</a> 
                </li>
                <li>
                    <a class="" href="<?= yii\helpers\Url::to(['/ayuda/videos-tutoriales']); ?>"><i class="fa fa-camera"></i> Videos</a>
                </li>
                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="user-image" alt="User Image"/>
                        <span class="hidden-xs">
                            <?php
                            $user = \Yii::$app->user->identity;
                            if(!empty($user->profile->nombre))
                                echo $user->profile->apellido . ", " .$user->profile->nombre;
                            else{
                                if(isset($user->username))
                                    echo ($user->username);
                                else
                                    echo "colocar nombre";
                            }
                            ?>
                        </span>
                    </a>
                    <ul class="dropdown-menu">
                        <!-- User image -->
                        <li class="user-header">
                            <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle"
                                 alt="User Image"/>

                            <p>
                                <?php
                            $user = \Yii::$app->user->identity;
                            if(!empty($user->profile->nombre))
                                echo $user->profile->apellido . ", " .$user->profile->nombre;
                            else{
                                if(isset($user->username))
                                    echo ($user->username);
                                else
                                    echo "colocar nombre";
                            }
                            ?>
                               
                                <small>
                                    <?php
                                      /*  $user = \Yii::$app->user->identity;
                                        $roles =  $user->misRoles;
                                        if(!empty($roles))
                                            foreach($roles as $rol)
                                                echo $rol->name . " ";
                                  */  ?> 
                                </small>
                            </p>
                        </li>
                        
                        <!-- Menu Footer-->
                        <li class="user-footer">
                            <div class="pull-left">
                                <a href="<?= yii\helpers\Url::to(['/user/settings/profile']);?>" class="btn btn-default btn-flat"> Perfil </a>
                            </div>
                            <div class="pull-right">
                                <?= Html::a(
                                    'Salir',
                                    ['/site/logout'],
                                    ['data-method' => 'post', 'class' => 'btn btn-default btn-flat']
                                ) ?>
                            </div>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="<?= yii\helpers\Url::to (['/user/security/logout']); ?>" data-method="post"><i class="fa fa-circle-o"> Salir</i></a>
                </li>
                <!-- User Account: style can be found in dropdown.less -->
                
            </ul>
        </div>
    </nav>
</header>