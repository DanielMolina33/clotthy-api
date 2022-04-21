<?php

namespace App\Http\Controllers\email;

use Mail;

class Emails {
    function send($to, $subject, $template, $data){
        try {
            Mail::send($template, $data, function($message) use ($to, $subject) {
                $message->to($to)
                ->subject($subject);
                $message->from('evegongora@clotthy.com', 'Clotthy');
            });
        } catch(\Exception $e){
            return [
                'res' => [
                    'message' => 'Hubo un error al enviar el correo electronico, intentalo de nuevo',
                    'error' => $e->getMessage(),
                ],
                'status' => 500
            ];
        }

        return [
            'res' => [
                'message' => 'Correo electronico enviado correctamente',
            ],
            'status' => 200
        ];
    }
}
