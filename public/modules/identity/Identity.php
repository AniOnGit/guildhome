<?php

class Identity {
    
    public function getCurrentIdentity() {
        return 0;
    }
    
    function getCurrentAvatar() {
        $settings = new Settings();
        $avatar = $settings->getSettingByKey('avatar');
        return $avatar;
    }

    function getAvatarByUserId($user_id) {
        $settings = new Settings();
        $avatar = $settings->getSettingByKey('avatar', $user_id);
        if ($avatar !== false AND !empty($avatar)) {
            return $avatar;
        }
        return '/themes/eol/images/guild_avatar.png';
    }
    
    function getIdentityById($user_id, $identity = 0) {
        $settings = new Settings();
        $profile = new Profile();

        $display_name = $settings->getSettingByKey('display_name', $user_id);
        if ($display_name !== false AND !empty($display_name)) {
            return $display_name;
        }

        if ($identity == 0) {
            $user = $profile->getUsers($user_id)[0];
            return $user->username;
        }
    }
    
}
