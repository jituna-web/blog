<?php
declare(strict_types=1);
namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

final class PostPresenter extends Nette\Application\UI\Presenter {
    private Nette\Database\Explorer $database;

    public function __construct(Nette\Database\Explorer $database) {
        $this->database = $database;
    }
    public function renderShow(int $postId):void {
        
         $post = $this->database->table('posts')->get($postId);
         if (!$post){
             $this->error('Stránka nebyla nalezena');
         }
         $this->template->post = $post;
         //$this->template->comments = $post->related('comments')->order('created_at');
    }
    protected function createComponentCommentForm(): Form{
        $form = new Form;

        $form->addText('name', 'Jméno:')
            ->setRequired();

            $form->addEmail('email', 'Email:');

            $form->addTextArea('content', 'Komentář:')
                ->setRequired();

                $form->addSubmit('send', 'Zveřejnit komentář');
                $form->onSuccess[]=[$this, 'commentFormSucceeded'];

                return $form;
    }
    public function commentFormSucceeded (\stdClass $values): void{
        $postId = $this->getParameter('postId');

        $this->database->table('comments')->insert([
            'post_id' => $postId,
            'name' => $values->name,
            'email' => $values->email,
            'content' => $values->content
        ]);

        $this->flashMessage('Děkuji za váš komentář', 'success');
        $this->redirect('this');
    }
    protected function createComponentPostForm():Form{
        $form = new Form;
        $form->addText('title', 'Titulek:') ->setRequired();
        $form->addTextArea('content', 'Obsah:')->setRequired();
        $form->addSubmit('send', 'Uložit a publikovat');
        $form->onSuccess[]=[$this, 'postFormSucceeded'];

        return $form;
    }
    public function postFormSucceeded(array $values): void{
        $postId=$this->getParameter('postId');
        if 
            (!$this->getUser()->isLoggedIn()){
                $this->error('Pro vytvoření, nebo editování příspěvku se musíte přihlásit.');
            }
        if ($postId){
            $post=$this->database->table('posts')->get($postId);
            $post->update($values);
        } else{
            $post=$this->database->table('posts')->insert($values);
        }

        $this->flashMessage("Příspěvek byl úspěšně zveřejněn", 'success');
        $this->redirect('show', $post->id);
    }
    public function actionEdit(int $postId): void{
        if (!$this->getUser()->isLoggedIn()){
            $this->redirect('Sign:in');
        }
        $post = $this->database->table('posts')->get($postId);
        if(!$post){
            $this->error('Příspěvěk nebyl nalezen');
        }
        $this['postForm']->setDefaults($post->toArray());
    }
    public function actionCreate():void{
        if (!$this->getUser()->isLoggedIn()){
            $this->redirect('Sign:in');
        }
    }
}