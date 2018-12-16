<?php

require 'app/components/functions.php';

class Petitions
{
    // TODO: $dbh ???

    public static function getPetitionById($id)
    {
        $dbh = Db::getConnection();

        $sth = $dbh->prepare(
            'SELECT p.*, users.email AS author 
            FROM petitions AS p
            LEFT JOIN users
              ON p.user_id = users.id
            WHERE p.id = :petitionId'
        );
        $sth->bindValue(':petitionId', $id);
        $sth->execute();
        return $sth->fetch(PDO::FETCH_OBJ);
    }

    public static function getPetitionsList()
    {
        $dbh = Db::getConnection();

        $sql = 'SELECT petitions.*, users.email AS author_email
			FROM petitions
			LEFT JOIN users 
			  ON (petitions.user_id = users.id)
            LEFT JOIN state_of_petitions AS statePetition
              ON (petitions.id = statePetition.petition_id)
            WHERE statePetition.active = 1
			';
        $sth = $dbh->prepare($sql);
        $sth->execute();
        return $sth->fetchAll(PDO::FETCH_OBJ);
    }


    // Добавление петиции.
    public static function addPetition()
    {
        $dbh = Db::getConnection();

        if (!empty($_POST)){
            if (isset($_POST['btnSubmit'])
                && !empty($_POST['btnSubmit'])
                && !empty($_POST['title'])
                && !empty($_POST['email'])
                && !empty($_POST['description'])
            ) {

                // Есть ли зарегестрированный автор (по емайл).
                $sth = $dbh->prepare(
                    "SELECT * FROM users
                WHERE email = :email"
                );
                $sth->bindValue(':email', $_POST['email']);
                $sth->execute();
                $userEmail = $sth->fetch(PDO::FETCH_ASSOC);

                if (empty($userEmail)) {
                    // Добавление нового автора (емайл).
                    $sth = $dbh->prepare(
                        "INSERT INTO users (email)
                    VALUES (:email)"
                    );
                    $sth->bindValue(':email', $_POST['email']);
                    $sth->execute();
                    // новый запрос для получения id user email
                    $sth = $dbh->prepare(
                        "SELECT * FROM users
                    WHERE email = :email"
                    );
                    $sth->bindValue(':email', $_POST['email']);
                    $sth->execute();
                    $userEmail = $sth->fetch(PDO::FETCH_ASSOC);
                }
                // Добавление петиции.
                $sth = $dbh->prepare(
                    "INSERT INTO petitions (title, user_id, description)
                      VALUES (:title, :user_id, :description)"
                );
                $sth->bindValue(':title', $_POST['title']);
                $sth->bindValue(':user_id', $userEmail['id']);
                $sth->bindValue(':description', $_POST['description']);
                $sth->execute();
                // Получение id петиции.
                $sth = $dbh->prepare(
                    "SELECT * FROM petitions
                WHERE title = :title
                AND user_id = :user_id"
                );
                $sth->bindValue(':title', $_POST['title']);
                $sth->bindValue(':user_id', $userEmail['id']);
                $sth->execute();
                $petition = $sth->fetch(PDO::FETCH_ASSOC);
                // Добавление петиции в таблицу состояний.
                $sth = $dbh->prepare(
                    "INSERT INTO state_of_petitions 
                    (user_id, petition_id, activationKey)
                VALUES (:user_id, :petition_id, :activationKey)"
                );
                $token = uniqid();
                $sth->bindValue(':user_id', $userEmail['id']);
                $sth->bindValue(':petition_id', $petition['id']);
                $sth->bindValue(':activationKey', $token);
                $result = $sth->execute();
                if ($result) {
                    sendMail($userEmail['email'], $petition['id'], $token);
                }
                // $_SESSION['message'] = 'addSuccess';

                // echo "<script>";
                // echo "window.location=document.URL;";
                // echo "</script>";
                return true;
            }

        }

        return false;
    }


    // Подписать петицию.
    public static function signPetition(){
        if (!empty($_POST['subsPetitionId'])
            && !empty($_POST['subsEmail'])) {

            if (!Petitions::IsAlreadySigned(
                    $_POST['subsPetitionId'],
                    $_POST['subsEmail']))
            {
                Petitions::SignThePetition(
                    $_POST['subsPetitionId'],
                    $_POST['subsEmail']);
                Petitions::SessionUpdate('signSuccess');
            }
            else{
                Petitions::SessionUpdate('signExists');
            }

        }
    }


    public static function activationPetition($params)
    {
        $dbh = Db::getConnection();

        if (isset($params['id']) && isset($params['token'])) {

            $petitionId = $params['id'];
            $token = $params['token'];
            $sth = $dbh->prepare(
                "SELECT * FROM state_of_petitions
            WHERE petition_id = :petitionId"
            );
            $sth->bindValue(':petitionId', $petitionId);
            $sth->execute();
            $petitionState = $sth->fetch(PDO::FETCH_ASSOC);

            if (!empty($petitionState)) {
                if ($petitionState['activationKey'] == $token) {
                    if (Petitions::activationOfThePetition($petitionId)){
                        return true;
                    }
                    else{
//                        header('Location: index.php?page=1');
                        return false;
                    }
                }
                else{
                    return false;
                }
            }
        }
    }



    //
    // Приватные методы.
    //


    private static function IsAlreadySigned($petitionId, $subsEmail){

        $dbh = Db::getConnection();

        // Узнаем есть ли голос этого емайл за данную петицию.
        $sth = $dbh->prepare(
            'SELECT list.*, u.email AS userEmail
                FROM list_of_votes AS list
                LEFT JOIN users AS u
                  ON list.user_id = u.id
                WHERE u.email = :subsEmail
                AND list.petition_id = :pId'
        );
        $sth->bindValue(':pId', $petitionId);
        $sth->bindValue(':subsEmail', $subsEmail);
        $sth->execute();
        $votes = $sth->fetch(PDO::FETCH_ASSOC);
        if ($votes['petition_id'] == $petitionId
            && $votes['userEmail'] == $subsEmail){
            return true;
        }
        else{
            return false;
        }
    }


    private static function SignThePetition($petitionId, $subsEmail){

        $dbh = Db::getConnection();

        // Петиция за которую голосуем.
        $sth = $dbh->prepare(
            "SELECT * FROM petitions AS p
            WHERE p.id = :pId"
        );
        $sth->bindValue(':pId', $petitionId);
        $sth->execute();
        $petition = $sth->fetch(PDO::FETCH_ASSOC);
        $newCount = $petition['countOfVotes'] + 1;
        // Увеличиваем кол-во голосов на 1.
        $sth = $dbh->prepare(
            'UPDATE petitions AS p
             SET p.countOfVotes = :newCount
             WHERE p.id = :pId'
        );
        $sth->bindValue(':pId', $petitionId);
        $sth->bindValue(':newCount', $newCount);
        $sth->execute();
        if (!Petitions::IsEmailExists($_POST['subsEmail'])){
            Petitions::AddNewEmail($_POST['subsEmail']);
        }
        // Получим id пользователя по email.
        $userId = Petitions::GetEmailId($subsEmail);
        // Закрепляем емайл за петицией.
        Petitions::ReserveEmailForPetition($petitionId, $userId);
    }


    private static function SessionUpdate($messageStatus){
        $_SESSION['message'] = $messageStatus;
        echo "<script>";
        echo "window.location=document.URL;";
        echo "</script>";
    }


    private static function IsEmailExists($email){

        $dbh = Db::getConnection();

        // Есть ли зарегестрированный автор (по емайл).
        $sth = $dbh->prepare(
            "SELECT * FROM users
            WHERE email = :email"
        );
        $sth->bindValue(':email', $email);
        $sth->execute();
        return $sth->fetch(PDO::FETCH_ASSOC);
    }


    private static function AddNewEmail($newEmail){

        $dbh = Db::getConnection();

        // Записываем новый емайл в таблицу.
        $sth = $dbh->prepare(
            "INSERT INTO users (email)
                VALUES (:email)"
        );
        $sth->bindValue(':email', $newEmail);
        return $sth->execute();
    }

    private static function GetEmailId($subsEmail){

        $dbh = Db::getConnection();

        $sth = $dbh->prepare(
            'SELECT id FROM users
          WHERE email = :subsEmail'
        );
        $sth->bindValue(':subsEmail', $subsEmail);
        $sth->execute();
        $userId = $sth->fetch(PDO::FETCH_ASSOC);
        return $userId['id'];
    }

    private static function ReserveEmailForPetition($petitionId, $userId){

        $dbh = Db::getConnection();

        $sth = $dbh->prepare(
            'INSERT INTO list_of_votes (user_id, petition_id) 
            VALUES (:userId, :pId)'
        );
        $sth->bindValue(':userId', $userId);
        $sth->bindValue(':pId', $petitionId);
        return $sth->execute();
    }

    private static function activationOfThePetition($petitionId){

        $dbh = Db::getConnection();

        // Активация петиции.
        $sth = $dbh->prepare(
            'UPDATE state_of_petitions
                    SET active = 1
                    WHERE petition_id = :petitionId'
        );
        $sth->bindValue(':petitionId', $petitionId);
        return $sth->execute();
    }

}