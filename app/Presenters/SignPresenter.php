<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;
use Nette\ComponentModel\IComponent;

final class SignPresenter extends Nette\Application\UI\Presenter {
    protected function createComponentSignInForm(): Form
    {
        $form=new Form;
        $form->addText('username', 'Uživatelské jméno')
        ->setRequired('Vyplňte uživatelské jméno');

        $form->addPassword('password', 'Heslo')
        ->setRequired('Vyplňte své heslo');

        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[]=[$this, 'signInFormSucceeded'];
        return $form;
    }
    public function signInFormSucceeded(Form $form, \stdClass $values): void{
        try {
            $this->getUser()->login($values->username, $values->password);
            $this->redirect('Homepage:');
        } catch (Nette\Security\AuthenticationException $e){
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }
    }
    public function actionOut():void{
        $this->getUser()->logout();
        $this->flashMessage('Odhlášení proběhlo úspěšně');
        $this->redirect('Homepage');
    }
}
?>
