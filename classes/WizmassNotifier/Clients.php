<?php
/**
 * Created by PhpStorm.
 * User: birger.roy@gmail.com
 * Date: 12/2/2015
 * Time: 2:30 PM
 */


namespace WizmassNotifier;


class Clients {

    public function addClient(\ElggUser $client)
    {
        $tokens = $this->live_notifier_get_token_service();
        $token = $tokens->getToken($client);

        if (!$token) {
            $token = $tokens->createToken($client);
        }

        return $token;
    }


    /**
     *
     */

    private function live_notifier_get_token_service() {
        global $CONFIG;

        $db = _elgg_services()->db;

        return new \WizmassNotifier\Tokens($db, $CONFIG);
    }
}