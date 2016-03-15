<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

$response = [
    'result' => [
        'leo'    => [],
        'google' => []
    ]
];
$word     = $_REQUEST['q'];
if (!empty($word))
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://dict.leo.org/dictQuery/m-vocab/ende/query.xml?lang=en&search=" . urlencode($word));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $curlResp = curl_exec($curl);
    curl_close($curl);
    $result = simplexml_load_string($curlResp) or die('Error loading data');
    echo "<pre>";
//print_r($result->sectionlist->section);
    if (isset($result->sectionlist->section))
    {
        foreach ($result->sectionlist->section as $section)
        {
            foreach ($section->entry as $trans)
            {
                $englishWord = new stdClass();
                foreach ($trans->side as $t)
                {
                    if ($t->attributes()->lang == "en")
                    {
                        $englishWord->en = '';
                        $res         = (array) $t->repr;
                        if (isset($res[0]) && is_string($res[0]))
                        {
                            $englishWord->en = $res[0];
                            //$response['result']['leo'][] = $res[0];
                        }
                        else
                        {
                            $res = preg_split("/<repr>|<small>/", $t->repr->asXML());
                            if (!empty($res) && count($res) > 1)
                            {
                                $englishWord->en                 = strip_tags($res[1]);
                                //$response['result']['leo'][] = strip_tags($res[1]);
                            }
                        }
                    }
                    if ($t->attributes()->lang == "de" && !empty($englishWord->en))
                    {
                        $res = (array) $t->repr;
                        if (isset($res[0]) && is_string($res[0]))
                        {
                            $englishWord->de = $res[0];
                            $response['result']['leo'][] = $englishWord;
                        }
                        else
                        {
                            $res = preg_split("/<repr>|<small>/", $t->repr->asXML());
                            if (!empty($res) && count($res) > 1)
                            {
                                $englishWord->de = strip_tags($res[1]);
                                $response['result']['leo'][] = $englishWord;
                            }
                        }
                        $englishWord = new stdClass();
                    }
                }
            }
        }
    }
}
else
{
    $response['error'] = 'Please provide query';
}
echo json_encode($response);
/*
 $googleURL = 'https://translate.google.com/translate_a/single?client=t&sl=de&tl=en&hl=en&dt=at&dt=bd&dt=ex&dt=ld&dt=md&dt=qca&dt=rw&dt=rm&dt=ss&dt=t&ie=UTF-8&oe=UTF-8&otf=2&srcrom=0&ssel=3&tsel=5&kc=4&tk=817709.677720&q='.urlencode($word);
   $curl = curl_init();
   curl_setopt ($curl, CURLOPT_URL, $googleURL);
   curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
   $curlResp = curl_exec ($curl);
   curl_close ($curl);
   $response['result']['google'] = explode('"', explode('[[["', $curlResp)[1])[0];
*/

?>

