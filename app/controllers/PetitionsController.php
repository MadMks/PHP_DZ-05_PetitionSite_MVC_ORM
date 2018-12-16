<?php

require ROOT . '/app/models/Petitions.php';

    class PetitionsController
    {
        public function IndexAction()
        {
            $title = "Петиции";
            $petitions = Petitions::getPetitionsList();

            $home = new View('index');
            $home->assign('petitions', $petitions);

            $layout = new View('layout');
            $layout->assign('title', $title);
            $layout->import('content', $home);
            $layout->display();
            return true;
        }

        public function ShowAction($params)
        {
            $petition = Petitions::getPetitionById($params['id']);
            $title = $petition->title;

            $home = new View('show');
            $home->assign('petition', $petition);

            $layout = new View('layout');
            $layout->assign('title', $title);
            $layout->import('content', $home);


            // Подписать петицию.
            if (isset($_POST['btnSubmit'])){
                Petitions::signPetition();
            }
            else{
                // Сообщение о выполнении.
                if (!empty($_SESSION['message'])) {
                    $message = new View('message');
                    $message->assign('status', $_SESSION['message']);
                    $home->import(
                        'messageStatus',
                        $message);
                    unset($_SESSION['message']);
                }
            }


            $layout->display();

            return true;
        }

        public function AddAction(){

            $title = "Добавление петиции";

            $home = new View('add');

            $layout = new View('layout');
            $layout->assign('title', $title);
            $layout->import('content', $home);


            // Добавление петиции.
            if (!empty($_POST)){
                if (Petitions::addPetition()) {
                    header('Location: /petitions/add?message=added');
                } else {
                    header('Location: /petitions/add?message=error');
                }
            }
            // else{
            //     // Сообщение о выполнении.
            //     if (!empty($_SESSION['message'])) {
            //         $message = new View('message');
            //         $message->assign('status', $_SESSION['message']);
            //         $home->import('messageStatus', $message);
            //         unset($_SESSION['message']);
            //     }
            // }

            // Выводим на экран.
            $layout->display();

            return true;
        }

        public function ActivationAction($params){

            $title = "Активация петиции";

            $home = new View('activation');

            // Активация.
            $message = new View('message');
            if (Petitions::activationPetition($params)){
                $message->assign('status', 'activateSuccess');
            }
            else{
                $message->assign('status', 'activateWarning');
            }
            $home->import('messageStatus', $message);

            $layout = new View('layout');
            $layout->assign('title', $title);
            $layout->import('content', $home);

            $layout->display();
        }
    }