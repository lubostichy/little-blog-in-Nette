<?php

namespace App\Presenters;

use Nette;
use App\Forms\SignFormFactory;


class SignPresenter extends BasePresenter
{
	/** @var SignFormFactory @inject */
	public $factory;


	/**
	 * Sign-in form factory.
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new Nette\Application\UI\Form;
                $form->addText('username', 'Uživatelské meno:')
                        ->setRequired('Prosím, zadajte svoje uživatelské meno.');
                $form->addPassword('password', 'Heslo:')
                        ->setRequired('Prosím, zadajte svoje heslo.');
                $form->addCheckbox('remember', 'Zostať prihlásený');
                $form->addSubmit('send', 'Prihlásiť');
                $form->onSuccess[] = array($this, 'signInFormSucceeded');
		return $form;
	}

        public function signInFormSucceeded($form)
        {
            $values = $form->values;
            
            try
            {
                $this->getUser()->login($values->username, $values->password);
                $this->redirect('Homepage:');
            } 
            catch (Nette\Security\AuthenticationException $e) 
            {
                $form->addError('Nesprávne prihlasovacie meno alebo heslo.');
            }
        }

        public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('You have been signed out.');
		$this->redirect('in');
	}

}
