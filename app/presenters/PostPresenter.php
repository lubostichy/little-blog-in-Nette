<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI;

class PostPresenter extends BasePresenter
{
    private $database;
    
    public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }
    
    public function renderShow($postId)
    {
        $post = $this->database->table('posts')->get($postId);
        
        if (!$post)
        {
            $this->error('Stránka sa nenašla.');         
        }
        
        $this->template->post = $post;
        $this->template->comments = $post->related('comment')->order('created_at');
    }
    
    protected function createComponentCommentForm()
    {
        $form = new UI\Form;
        
        $form->addText('name', 'Meno:')
                ->setRequired();
        $form->addText('email', 'Email:');
        $form->addTextArea('content', 'Komentár:');
        $form->addSubmit('send','Publikovať komentár');
        $form->onSuccess[] = array($this, 'commentFormSucceeded');
        return $form;             
    }
    
    public function commentFormSucceeded($form, $values)
    {
        $postId = $this->getParameter('postId');
        
        $this->database->table('comments')->insert(array(
            'post_id' => $postID,
            'name' => $values->name,
            'email' => $values->email,
            'content' => $values->content,
        ));
        
        $this->flashMessage('Ďakujem za komentár', 'success');
        $this->redirect('this');
    }
    
    public function createComponentPostForm()
    {
        $form = new UI\Form;
        $form->addText('title', 'Titulok:')
            ->setRequired();
        $form->addTextArea('content', 'Obsah:')
            ->setRequired();

        $form->addSubmit('send', 'Uložiť a publikovať');
        $form->onSuccess[] = array($this, 'postFormSucceeded');

        return $form;
    }
    
    public function postFormSucceeded($form, $values)
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error('Pre vytvorenie alebo pre úpravu príspevku je nutné sa prihlásiť.');
        }
        
        $postId = $this->getParameter('postId');
        
        if ($postId)
        {
            $post = $this->database->table('posts')->get($postId);
            $post->update($values);
        } 
        else 
        {
            $post = $this->database->table('posts')->insert($values);
        }

        $this->flashMessage("Príspevok bol úspešne publikovaný.", 'success');
        $this->redirect('show', $post->id);
    }
    
    public function actionEdit($postId)
    {
        if (!$this->getUser()->isLoggedIn()) 
        {
            $this->redirect('Sign:in');
        }
        $post = $this->database->table('posts')->get($postId);
        if (!$post)
        {
            $this->error('Príspevok sa nenašiel');
        }
        $this['postForm']->setDefaults($post->toArray());
    }
    
    public function actionCreate()
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Sign:in');
        }
    }
}