<?php
class gw2api_Widgets {

    function buildSorter($key) {
       return function ($a, $b) use ($key) {
            return strnatcmp($a[$key], $b[$key]);
        };
    }
    
    function getNextBirthdays() {
        $settings = new Settings();
        $characters = json_decode($settings->getSettingByKey('gw2apidata'), true);
        $msg = Msg::getInstance();
        
        if (isset($characters['characters']) AND is_array($characters['characters'])) {
            foreach ($characters['characters'] as $character) {
                if (!isset($character['birthday'])) {
                    $birthday = new DateTime();
                    $msg->add('api_data_outdated', 'Your api-data seems to be outdated. Please re-import your api data!');
                } elseif (is_array($character['birthday'])) {
                    $birthday = new DateTime($character['birthday']['date']);
                    $msg->add('api_data_outdated', 'Your api-data seems to be outdated. Please re-import your api data!');
                } else {
                    $birthday = new DateTime($character['birthday']);
                }
                
                $next_birthday = $birthday;
                $next_birthday->setDate(date("Y"), $birthday->format("m"), $birthday->format("d"));
                $now = new DateTime();
                if ($next_birthday < $now) {
                    $next_birthday->setDate(date("Y") + 1, $birthday->format("m"), $birthday->format("d"));
                }

                $days_to_next_birthday = $next_birthday->diff($now);

                $characters_birthdays[] = array(
                    'name' => $character['name'],
                    'birthday_in' => $days_to_next_birthday->days,
                );
            }
            usort($characters_birthdays, $this->buildSorter('birthday_in'));
            return $characters_birthdays;
        } else {
            return false;
        }
    }
    
    function getNextBirthdaysView() {
        $characters_birthdays = $this->getNextBirthdays();
        $msg = Msg::getInstance();
        if ($characters_birthdays !== false) {
            $characters_birthdays = array_slice($characters_birthdays, 0, 3);
            $view = new View();
            $view->setTmpl($view->loadFile('/views/gw2api/widget/next_character_birthdays_view.php'));
            $subView = new View();
            $subView->setTmpl($view->getSubTemplate('{##widget_title##}'));
            $subView->addContent('{##widget_title_text##}', 'Upcoming birthdays');
            $subView->replaceTags();
            $view->addContent('{##widget_title##}', $subView);
            $view->addContent('{##widget_warning##}', $msg->fetch('api_data_outdated'));
            $all_characters = null;
            if (is_array($characters_birthdays)) {
                foreach ($characters_birthdays as $character) {
                    $option = new View();
                    $option->setTmpl($view->getSubTemplate('{##characters##}'));
                    $option->addContent('{##name##}', $character['name']);
                    $option->addContent('{##birthday_in##}', $character['birthday_in']);
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
}
