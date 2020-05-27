<?php

namespace app\helpers;

use Yii;


/**
 * Alert widget renders a message from session flash. All flash messages are displayed
 * in the sequence they were assigned using setFlash. You can set message as following:
 *
 * ```php
 * Yii::$app->session->setFlash('error', 'This is the message');
 * Yii::$app->session->setFlash('success', 'This is the message');
 * Yii::$app->session->setFlash('info', 'This is the message');
 * ```
 *
 * Multiple messages could be set as follows:
 *
 * ```php
 * Yii::$app->session->setFlash('error', ['Error 1', 'Error 2']);
 * ```
 *
 * @author Kartik Visweswaran <kartikv2@gmail.com>
 * @author Alexander Makarov <sam@rmcreative.ru>
 */
class Alert extends \yii\bootstrap\Widget {

    /**
     * @var array the alert types configuration for the flash messages.
     * This array is setup as $key => $value, where:
     * - $key is the name of the session flash variable
     * - $value is the bootstrap alert type (i.e. danger, success, info, warning)
     */
    public $alertTypes = [
        'error' => 'error',
        'danger' => 'error',
        'success' => 'success',
        'info' => 'info',
        'warning' => 'warning'
    ];
    public $alertIcons = [
        'error' => 'glyphicon glyphicon-alert',
        'danger' => 'glyphicon glyphicon-alert',
        'success' => 'glyphicon glyphicon-ok-sign',
        'info' => 'glyphicon glyphicon-info-sign',
        'warning' => 'glyphicon glyphicon-exclamation-sign'
    ];
    public $alertTitles = [
        'error' => 'Atención',
        'danger' => 'Atención',
        'success' => 'Éxito',
        'info' => 'Información',
        'warning' => 'Advertencia'
    ];

    /**
     * @var array the options for rendering the close button tag.
     */
    public $closeButton = [];

    public function init() {
        parent::init();

        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();
        $appendCss = isset($this->options['class']) ? ' ' . $this->options['class'] : '';

        foreach ($flashes as $type => $data) {
            if (isset($this->alertTypes[$type])) {
                $data = (array) $data;
                foreach ($data as $i => $message) {
                    /* initialize css class for each alert box */
                    $this->options['class'] = $this->alertTypes[$type] . $appendCss;

                    /* assign unique id to each alert box */
                    $this->options['id'] = $this->getId() . '-' . $type . '-' . $i;



                    $class = $this->options['class'];
                    $icon = $this->alertIcons[$type];
                    $title = $this->alertTitles[$type];

                    $notify ="<script type='text/javascript'>                                  
                                new PNotify({
                                    title:'$title',
                                    text: '$message',
                                    icon: '$icon',
                                    type: '$class'
                                });"
                            . "</script>";
                    echo $notify;
                }

                $session->removeFlash($type);
            }
        }
    }

}