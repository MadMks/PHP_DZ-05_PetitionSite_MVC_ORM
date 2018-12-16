<?php

    function sendMail($email, $petitionId, $token){
        $message = 'Перейдите по '
            .'<a href="http://localhost:81/petitions/'
            ."$petitionId&$token"
            .'">'
            .'ссылке</a>,'
            .' для активации петиции.';
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .= 'From: Admin <info@max.itstep.fun>' . "\r\n";
        if (mail(
            $email,
            "Подтверждение петиции",
            $message,
            $headers))
        {
            // echo "сообщение успешно отправлено";
        } else {
            // echo "при отправке сообщения возникли ошибки";
        }
    }


