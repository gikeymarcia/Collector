<?php

class Login
{
    public static function run($username, $ip, $debug_name, FileSystem $_files) {
        $user = preg_replace('([^ !#$%&\'()+,\\-.0-9;=@A-Z[\\]^_a-z~])', '', $username);
        if (strlen($user) < 4) throw new Exception('Username too short');
        
        // TODO: self::check_eligibility($user, $ip, $_files);

        $old_sess_id = $_files->query('PHP Session Table', $user);

        if ($old_sess_id === null) {
            $data = array(
                'Session' => 1,
                'Debug'   => self::check_debug_mode($user, $debug_name),
                '_FILES'  => $_files
            );
        } else {
            $data = self::reload_session($old_sess_id, $_files);
        }
        
        self::set_file_defaults($user, $data, $data['_FILES']);
        $_files->write('PHP Session Table', session_id(), $user);
        
        return $data;
    }
    
    private static function check_eligibility($user, $ip, FileSystem $_files) {
        $banned = $_files->get('Banned Users');
        
        if (isset($banned[$user])) {
            throw new Exception('Banned newb');
        }
        
        $blacklistedIPs = $_files->get('Blacklisted IPs');
        $whitelistedIPs = $_files->get('Whitelisted IPs');
        
        if (isset($blacklistedIPs[$ip]) && !isset($whitelistedIPs[$ip])) {
            throw new Exception('Banned IP');
        }
    }

    private static function reload_session($sessID, FileSystem $_files) {
        $data = $_files->read('Session', array('Session ID' => $sessID));

        self::validate_session($data);
        
        return $data;
    }

    private static function validate_session($data) {
        $minTime = $data['Min Time'];
        $maxTime = $data['Max Time'];
        $now     = time();

        if (!empty($maxTime) && $now > $maxTime) {
            throw new Exception('Too late!');
        }

        if ($now < $minTime) {
            throw new Exception('Too early!');
        }
    }

    private static function set_file_defaults($user, $data, FileSystem $_files) {
        $_files->set_default('Username', $user);
        $_files->set_default('SessionN', $data['Session']);
        $_files->set_default('ID',       rand_string(10));
    }

    private function check_debug_mode($user, $debugName) {
        return substr($user, 0, strlen($debugName)) === $debugName;
    }
}
