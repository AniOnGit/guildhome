<?php

class gw2api {
    
    function initEnv() {
        Toro::addRoute(["/gw2api" => "gw2api"]);
        Toro::addRoute(["/gw2api/:alpha" => "gw2api"]);
        
        Validation::registerValidation('api_key', array(new gw2api(), 'validateApiKey'));
    }
    
    function get($slug = '') {
        $page = Page::getInstance();
        
        $login = new Login();
        $settings = new Settings();

        $page->setContent('{##main##}', '<h2>Guild Wars 2 API Access</h2>');
        if ($login->isLoggedIn()) {
            $api_key = $settings->getSettingByKey('api_key');
            $page->addContent('{##main##}', $settings->getUpdateSettingForm('api_key', '/gw2api'));
            if ($api_key !== false) {
                $page->addContent('{##main##}', $this->getApiKeyScopeView());

                if ($login->isOperator()) {
                    if ($api_key !== false) {
                        $permissions = $this->getApiKeyScope();
                        if (is_array($permissions) AND in_array('guilds', $permissions)) {
                            $page->addContent('{##main##}', $settings->getUpdateSettingForm('main_guild_id', '/gw2api'));
                            $page->addContent('{##main##}', $this->getUpdateRosterForm());
                        }
                    } else {
                        $page->addContent('{##main##}', 'No Api key found');
                    }
                }
                
                $page->addContent('{##main##}', $this->getImportForm());
                if ($this->hasApiData()) {
                    $page->addContent('{##main##}', $this->getImportAccountnameForm());
                    $page->addContent('{##main##}', $this->getAccountCharactersView());
                    $page->addContent('{##main##}', $this->getRosterView());
                }
            } else {
                $page->addContent('{##main##}', 'No Api key found');
            }
        } else {
            header("Location: /activities");
        }
    }
    
    function post_xhr ($slug) {
        $login = new Login();
        $env = Env::getInstance();

        if ($login->isLoggedIn()) {
            if (isset($env->post('gw2api_import')['submit'])) {
                if ($slug == 'import') {
                    $this->storeApiData($this->fetchApiData());
                }
            }
        }
        
        $settings = new Settings();
        if (($api_data = $settings->getSettingByKey('gw2apidata')) !== false) {
            $created = new DateTime(json_decode($api_data, true)['created']);
            echo '(You have local data, imported on ' . $created->format("Y-m-d H:i:s").')';
        } else {
            echo 'Nothing fetched yet';
        }
        exit;
    }
    
    function post($slug = '') {
        $login = new Login();
        $env = Env::getInstance();
        
        if ($login->isLoggedIn()) {
            if (isset($env->post('gw2api_import_accountname')['submit'])) {
                if ($slug == 'import_accountname') {
                    $this->importAccountname();
                }
            }
            if (isset($env->post('gw2api_import')['submit'])) {
                if ($slug == 'import') {
                    $this->storeApiData($this->fetchApiData());
                }
            }
        } else {
            header("Location: /activities");
        }
        if ($login->isOperator()) {
            if (isset($env->post('gw2api_update_roster')['submit'])) {
                if ($slug == 'update_roster') {
                    $this->extractRosterFromDump();
                }
            }
        }
        if (is_string($env->post('target_url'))) {
            header("Location: " . $env->post('target_url'));
        }
        header("Location: /gw2api");
    }
    
    function validateApiKey($key = '') {
        $msg = Msg::getInstance();
        $error = 0;
        
        $api_tokeninfo = $this->gw2apiRequest('/v2/tokeninfo', $key);

        if (!isset($api_tokeninfo['permissions']) OR !is_array($api_tokeninfo['permissions'])) {
            $msg->add('setting_api_key_validation', 'Not a valid API-Key?');
            $error = 1;
        } else {
            $api_permissions = $api_tokeninfo['permissions'];
            if (!in_array('account', $api_permissions)) {
                $msg->add('setting_api_key_validation', 'Enable at least the "account" scope in your Api-Key.');
                $error = 1;
            }
            $test = array_intersect(['inventories', 'wallet', 'builds', 'tradingpost'], $api_permissions);
            if (count($test) != 0) {
                $msg->add('setting_api_key_validation', 'Please restrict your Api-Key to "account", "characters" and "guilds", thank you.');
                $error = 1;
            }
        }

        if ($error == 1) {
            return false;
        }
        $msg->add('setting_api_key_validation', 'API-Key valid and ready :)');
        return true;
    }
    
    function fetchApiData() {
        $settings = new Settings();
        $gw2apikey = $settings->getSettingByKey('api_key');
        
        $api_tokeninfo = $this->gw2apiRequest('/v2/tokeninfo', $gw2apikey);
        $api_permissions = $api_tokeninfo['permissions'];

        if (is_array($api_permissions) === false) {
            return false;
        }
        $api_data['permissions'] = $api_permissions;
        foreach ($api_permissions as $permission) {
            ${'api_' . $permission} =  $this->gw2apiRequest('/v2/' . $permission, $gw2apikey);
        }
        
        if (isset($api_characters) AND is_array($api_characters)) {
            foreach ($api_characters as $key => $value) {
                $characters[$key] = $this->gw2apiRequest('/v2/characters/' . rawurlencode($value), $gw2apikey);
                if (!empty($characters[$key]['guild'])) {
                    $characters[$key]['guild'] = $this->gw2apiRequest('/v1/guild_details.json?guild_id=' . $characters[$key]['guild'])['guild_name'];
                }
                $oDateNow = new DateTime();
                $oDateBirth = new DateTime($characters[$key]['created']);
                $oDateIntervall = $oDateNow->diff($oDateBirth, true);
                $characters[$key]['age'] = $oDateIntervall->format('%a');
                $birthday = new DateTime(date('Y-m-d', mktime(0, 0, 0, date("m") , date("d") - $characters[$key]["age"], date("Y"))));
                $characters[$key]['birthday'] = $birthday->format("Y-m-d");
            }
            $api_data['characters'] = $characters;
        }

        if (isset($api_account) AND is_array($api_account)) {
            $api_data['account'] = $api_account;
            if (is_array($api_account['guilds'])) {
                foreach ($api_account['guilds'] as $key => $guild) {
                    $api_data['guilds'][$key] = $this->gw2apiRequest('/v2/guild/' . $guild);
                    $login = new Login();
                    if ($login->isLoggedIn() AND $login->isAdmin()) {
                        $api_data['guilds'][$key]['roster'] = $this->gw2apiRequest('/v2/guild/' . $guild . '/members', $gw2apikey);
                    }
                }
            }
        }

        if (is_array($api_data)) {
            $created = new DateTime();
            $api_data['created'] = $created->format('Y-m-d H:i:s');
            return $api_data;
        }
        return false;
    }
    
    function storeApiData($api_data) {
        $settings = new Settings();
        if ($settings->updateSetting('gw2apidata', json_encode($api_data)) == true) {
            return true;
        }
        return false;
    }

    static function hasApiData($section = NULL) {
        $settings = new Settings();
        $api_data = $settings->getSettingByKey('gw2apidata');
        if ($api_data !== false) {
            $api_data = json_decode($api_data, true);
            if ($section === NULL) {
                if (isset($api_data['created'])) {
                    return true;
                }
            } else {
                if (isset($api_data[$section]) AND !empty($api_data[$section])) {
                    return true;
                }
            }
        }
        return false;
    }
    
    function getImportForm() {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/gw2api/import_form.php'));
        $view->addContent('{##form_action##}', '/gw2api/import');
        $view->addContent('{##gw2api_import_submit_text##}', 'Import from API');
        $settings = new Settings();
        if (($api_data = $settings->getSettingByKey('gw2apidata')) !== false) {
            $created = new DateTime(json_decode($api_data, true)['created']);
            $view->addContent('{##import_status##}', 'You have local data, imported on ' . $created->format("Y-m-d H:i:s"));
        } else {
            $view->addContent('{##import_status##}', 'Nothing fetched yet');
        }

        $view->replaceTags();
        return $view;
    }

    function getUpdateRosterForm() {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/gw2api/update_roster_form.php'));
        $view->addContent('{##form_action##}', '/gw2api/update_roster');
        $view->addContent('{##gw2api_update_roster_submit_text##}', 'Fetch roster');
        $view->replaceTags();
        return $view;
    }

    function importAccountname() {
        $settings = new Settings();
        if (($api_data = $settings->getSettingByKey('gw2apidata')) === false) {
            return false;
        }
        $api_data = json_decode($api_data, true);

        if (is_array($api_data['permissions']) === false OR !in_array('account', $api_data['permissions'])) {
            return false;
        }
        if ($settings->updateSetting('gw2_account', $api_data['account']['name']) === false) {
            return false;
        }

        return true;
    }
    
    function getImportAccountnameForm() {
        $view = new View();
        $view->setTmpl($view->loadFile('/views/gw2api/import_accountname_form.php'));
        $view->addContent('{##form_action##}', '/gw2api/import_accountname');
        $view->addContent('{##gw2api_import_accountname_submit_text##}', 'Fetch accountname');

        $settings = new Settings();
        if (($current_accountname = $settings->getSettingByKey('gw2_account')) !== false) {
            $view->addContent('{##current_accountname##}', $current_accountname);
        } else {
            $view->addContent('{##current_accountname##}', 'Nothing fetched yet');
        }

        $view->replaceTags();
        return $view;
    }

    function getApiKeyScope() {
        $settings = new Settings();
        $api_key = $settings->getSettingByKey('api_key');
        
        $api_tokeninfo = $this->gw2apiRequest('/v2/tokeninfo', $api_key);
        $api_permissions = $api_tokeninfo['permissions'];

        if (is_array($api_permissions) === false) {
            return false;
        }

        return $api_permissions;
    }
    
    function getApiKeyScopeView() {
        $scope = $this->getApiKeyScope();
        if (false !== $scope) {
            $view = new View();
            $view->setTmpl($view->loadFile('/views/gw2api/api_key_scope_view.php'));
            $all_permissions = null;
            if (is_array($scope)) {
                foreach ($scope as $permission) {
                    $option = new View();
                    $option->setTmpl($view->getSubTemplate('{##permissions##}'));
                    $option->addContent('{##permission##}', $permission);
                    $option->replaceTags();
                    $all_permissions .= $option;
                }
            }
            if (is_null($all_permissions)) {
                $view->addContent('{##permissions##}', 'There seems to be no data available.');
            } else {
                $view->addContent('{##permissions##}', $all_permissions);
            }
            $view->replaceTags();
            return $view;
        }
        return false;
    }
    
    
    function extractRosterFromDump() {
        $settings = new Settings();
        $roster = json_decode($settings->getSettingByKey('gw2apidata'), true);
        $roster = $roster['guilds'][0]['roster'];
        foreach ($roster as $member) {
            $account = $member['name'];
            $rank =  $member['rank'];
            $values[] = "('$account', '$rank')";
        }
        $values = implode(',', $values);
        
        $db = db::getInstance();

        $sql = "TRUNCATE TABLE api_roster;";
        $query = $db->query($sql);
        $sql = "INSERT INTO api_roster (account_name, guild_rank) VALUES $values;";

        $query = $db->query($sql);
        if ($query !== false) {
            return true;
        }
        return false;
    }
    
    function getRankUsageFromRoster() {
        $db = db::getInstance();

        $sql = "SELECT guild_rank, COUNT(*) as rank_count FROM api_roster GROUP BY guild_rank ORDER BY FIELD(guild_rank, 'Member', 'Chieftain', 'Officer', 'Leader') DESC, rank_count;";     

        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $rank_usage[] = $result_row;
            }
            return $rank_usage;
        }
        return false;
    }

    function getRankUsageFromRosterView() {
        $ranks = $this->getRankUsageFromRoster();
        if (FALSE !== $ranks) {
            $view = new View();
            $view->setTmpl($view->loadFile('/views/gw2api/rank_usage_view.php'));
            $all_ranks = null;
            if (is_array($ranks)) {
                foreach ($ranks as $rank) {
                    $option = new View();
                    $option->setTmpl($view->getSubTemplate('{##ranks##}'));
                    $option->addContent('{##rank_description##}', $rank->guild_rank);
                    $option->addContent('{##rank_count##}', $rank->rank_count);
                    $option->replaceTags();
                    $all_ranks .= $option;
                }
            }
            if (is_null($all_ranks)) {
                $view->addContent('{##ranks##}', 'There seems to be no data available.');
            } else {
                $view->addContent('{##ranks##}', $all_ranks);
            }
            $view->replaceTags();
            return $view;
        }
        return false;
    }

    function getRoster() {
        $db = db::getInstance();

        $sql = "SELECT * FROM api_roster ORDER BY FIELD(guild_rank, 'Member', 'Chieftain', 'Officer', 'Leader') DESC, guild_rank;";     

        $query = $db->query($sql);
        if ($query !== false AND $query->num_rows >= 1) {
            while ($result_row = $query->fetch_object()) {
                $roster[] = $result_row;
            }
            return $roster;
        }
        return false;
    }

    function getRosterView() {
        $roster = $this->getRoster();
        if (FALSE !== $roster) {
            $view = new View();
            $view->setTmpl($view->loadFile('/views/gw2api/roster_view.php'));
            $full_roster = null;
            if (is_array($roster)) {
                foreach ($roster as $member) {
                    $option = new View();
                    $option->setTmpl($view->getSubTemplate('{##roster##}'));
                    $option->addContent('{##account_name##}', $member->account_name);
                    $option->addContent('{##guild_rank##}', $member->guild_rank);
                    $option->replaceTags();
                    $full_roster .= $option;
                }
            }
            if (is_null($full_roster)) {
                $view->addContent('{##roster##}', 'There seems to be no data available.');
            } else {
                $view->addContent('{##roster##}', $full_roster);
            }
            $view->replaceTags();
            return $view;
        }
        return false;
    }

    function getAccountCharacters() {
        $settings = new Settings();
        $characters = json_decode($settings->getSettingByKey('gw2apidata'), true);
        $msg = Msg::getInstance();
        
        if (isset($characters['characters']) AND is_array($characters['characters'])) {
            foreach ($characters['characters'] as $character) {
                $name = $character['name'];
                $race = $character['race'];
                $gender = $character['gender'];
                $profession = $character['profession'];
                $level = $character['level'];
                $guild = $character['guild'];
                $age = $character['age'];
                $created = new DateTime($character['created']);
                $deaths = $character['deaths'];
                
                if (!isset($character['birthday'])) {
                    $birthday = new DateTime();
                    $msg->add('api_data_outdated', 'Your api-data seems to be outdated. Please re-import your api data!');
                } elseif (is_array($character['birthday'])) {
                    $birthday = new DateTime($character['birthday']['date']);
                    $msg->add('api_data_outdated', 'Your api-data seems to be outdated. Please re-import your api data!');
                } else {
                    $birthday = new DateTime($character['birthday']);
                }
                
                if (is_string($birthday)) {
                    $next_birthday = new DateTime();
                } else {
                    $next_birthday = $birthday;
                    $next_birthday->setDate(date("Y"), $birthday->format("m"), $birthday->format("d"));
                }
                
                $now = new DateTime();
                if ($next_birthday < $now) {
                    $next_birthday->setDate(date("Y") +1, $birthday->format("m"), $birthday->format("d"));
                }

                $days_to_next_birthday = $next_birthday->diff($now);

                $characters_modified[] = array(
                    'name' => $name,
                    'race' => $race,
                    'gender' => $gender,
                    'profession' => $profession,
                    'level' => $level,
                    'guild' => $guild,
                    'age' => $age,
                    'birthday' => $birthday->format("Y-m-d"),
                    'birthday_in' => $days_to_next_birthday->days,
                    'created' => $created->format("Y-m-d"),
                    'deaths' => $deaths,
                );

            }

            return $characters_modified;
        } else {
            return false;
        }

    }

    function getAccountCharactersView() {
        $characters = $this->getAccountCharacters();
        $msg = Msg::getInstance();
        
        if ($characters !== false) {
            $view = new View();
            $view->setTmpl($view->loadFile('/views/gw2api/account_characters_view.php'));
            $view->addContent('{##warning##}', $msg->fetch('api_data_outdated'));
            $all_characters = null;
            if (is_array($characters)) {
                foreach ($characters as $character) {
                    $option = new View();
                    $option->setTmpl($view->getSubTemplate('{##characters##}'));
                    $option->addContent('{##level##}', $character['level']);
                    $option->addContent('{##name##}', $character['name']);
                    $option->addContent('{##race##}', $character['race']);
                    $option->addContent('{##gender##}', $character['gender']);
                    $option->addContent('{##guild##}', $character['guild']);
                    $option->addContent('{##age##}', $character['age']);
                    $option->addContent('{##birthday_in##}', $character['birthday_in']);
                    $option->addContent('{##birthday##}', $character['birthday']);
                    $option->addContent('{##created##}', $character['created']);
                    $option->addContent('{##deaths##}', $character['deaths']);
                    $option->replaceTags();
                    $all_characters .= $option;
                }
            }
            if (is_null($all_characters)) {
                $view->addContent('{##characters##}', 'There seems to be no data available.');
            } else {
                $view->addContent('{##characters##}', $all_characters);
            }
            $view->replaceTags();
            return $view;
        }
        return false;
    }

    function gw2apiRequest($request, $api_key = "") {
        $log = Logger::getInstance();
        
        if ($api_key != "") {
            $pattern = "/^[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{20}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}$/";
            if (!preg_match($pattern, $api_key)) {
                return false;
            }
        }

        $url = parse_url('https://api.guildwars2.com' . $request);
        if (!$fp = @fsockopen('ssl://' . $url['host'], 443, $errno, $errstr, 5)) {
            $log::lwrite("$errstr ($errno)");
            return false;
        }

        $nl = "\r\n";
        $query = (isset($url['query']) ? '?' . $url['query'] : '');
        
        $header = 'GET ' . $url['path'] . $query . ' HTTP/1.1' . $nl;
        $header .= 'Host: ' . $url['host'] . $nl;
        $header .= !empty($api_key) ? 'Authorization: Bearer ' . $api_key . $nl : '';
        $header .= 'Connection: Close' . $nl . $nl;

        fwrite($fp, $header);
        stream_set_timeout($fp, 5);

        $response = '';
        $eof = false;
        do {
            $in = fread($fp, 1024 * 8);
            if (strlen($in) == 0) {
                $eof = true;
            } else {
                $response .= $in;
            }
        } while ($eof === false);

//        $log::lwrite($response . $nl);
        $response_lines = explode($nl, $response);
        if (isset($response_lines[0]) && $response_lines[0] == 'HTTP/1.1 200 OK') {
            // gw2 api sends their actual api-data in the last line
            $api_response = json_decode($response_lines[count($response_lines) - 1], true);
            return $api_response;
        }
        return false;
    }

}
$gw2api = new gw2api();
$gw2api->initEnv();
unset($gw2api);