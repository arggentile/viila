<?php

/*
 * This file is part of the 2amigos/yii2-usuario project.
 *
 * (c) 2amigOS! <http://2amigos.us/>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace app\controllers\user;

use Da\User\Event\FormEvent;
use Da\User\Event\ResetPasswordEvent;
use Da\User\Factory\MailFactory;
use Da\User\Form\RecoveryForm;
use Da\User\Model\Token;
use Da\User\Module;
use Da\User\Query\TokenQuery;
use Da\User\Query\UserQuery;
use Da\User\Service\PasswordRecoveryService;
use Da\User\Service\ResetPasswordService;
use Da\User\Traits\ContainerAwareTrait;
use Da\User\Traits\ModuleAwareTrait;
use Da\User\Validator\AjaxRequestModelValidator;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;


use Da\User\Controller\RecoveryController as BaseRecoveryController;

class RecoveryController extends BaseRecoveryController
{
 
    /**
     * Displays / handles user password recovery request.
     *
     * @throws NotFoundHttpException
     * @throws InvalidConfigException
     * @throws InvalidParamException
     * @return string
     *
     */
    public function actionRequest()
    {
        if (!$this->module->allowPasswordRecovery) {
            throw new NotFoundHttpException();
        }

        /** @var RecoveryForm $form */
        $form = $this->make(RecoveryForm::class, [], ['scenario' => RecoveryForm::SCENARIO_REQUEST]);

        $event = $this->make(FormEvent::class, [$form]);

        $this->make(AjaxRequestModelValidator::class, [$form])->validate();

        if ($form->load(Yii::$app->request->post()) && $form->validate()) {
            $this->trigger(FormEvent::EVENT_BEFORE_REQUEST, $event);

            $mailService = MailFactory::makeRecoveryMailerService($form->email);

            if ($this->make(PasswordRecoveryService::class, [$form->email, $mailService])->run()) {
                $this->trigger(FormEvent::EVENT_AFTER_REQUEST, $event);
            }

            return $this->renderPartial(
                '/shared/message',
                [
                    'title' => Yii::t('usuario', 'Recovery message sent'),
                    'module' => $this->module,
                ]
            );
        }

        return $this->renderPartial('request', ['model' => $form]);
    }


}
