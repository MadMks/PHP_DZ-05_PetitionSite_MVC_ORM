<?php
require ROOT . '/app/models/Petitions.php';

    class HomeController
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
    }