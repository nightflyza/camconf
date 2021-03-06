<?php

include ('api/autoloader.php');
define('MODELS_PATH', './models/');
$usageNotice = 'Usage: php camconf.php --model=[model] or php camconf.php --listmodels';

if (ubRouting::optionCliCount() == 2) {
    if (ubRouting::optionCliCheck('model') OR ubRouting::optionCliCheck('listmodels', false)) {
        $camModel = ubRouting::optionCli('model');

        if (ubRouting::optionCliCheck('model')) {
            if (!empty($camModel)) {
                if (file_exists(MODELS_PATH . $camModel)) {
                    $modelConfig = parse_ini_file(MODELS_PATH . $camModel);
                    $cameraInterface = new OmaeUrl();

                    //login to the camera
                    $baseUrl = $modelConfig['PROTO'] . '://' . $modelConfig['DEFAULT_IP'];
                    if ($modelConfig['AUTH'] == 'basic') {
                        $cameraInterface->setBasicAuth($modelConfig['LOGIN'], $modelConfig['PASSWORD']);
                    }

                    $cameraInterface->setHeadersReturn(true);

                    $login = $cameraInterface->response($baseUrl);
                    if (ispos($login, $modelConfig['MARK_LOGIN_OK'])) {
                        show_window('', 'Login: OK');
                        if (isset($modelConfig['METHOD']) and isset($modelConfig['VARS'])) {
                            $modelVars = $modelConfig['VARS'];
                            if (!empty($modelVars)) {
                                $modelVars = explode(",", $modelVars);
                                if (!empty($modelVars)) {
                                    foreach ($modelVars as $io => $eachVar) {
                                        if ($modelConfig['METHOD'] == 'POST') {
                                            $eachVar = explode(':', $eachVar);
                                            $cameraInterface->dataPost($eachVar[0], $eachVar[1]);
                                            print($eachVar[0].'->'.$eachVar[1].PHP_EOL);
                                        }
                                    }

                                    //changing data
                                    $netConfResult = $cameraInterface->response($baseUrl . '/' . $modelConfig['URL_NET']);
                                }
                            }
                        }
                    } else {
                        show_error('Login to ' . $modelConfig['DEFAULT_IP'] . ' FAILED');
                    }
                } else {
                    show_error('Unknown camera model');
                }
            }
        }

        if (ubRouting::optionCliCheck('listmodels', false)) {
            $allModels = scandir(MODELS_PATH);
            if (sizeof($allModels) > 2) {
                foreach ($allModels as $io => $eachModel) {
                    if ($eachModel != '.' AND $eachModel != '..') {
                        print($eachModel . PHP_EOL);
                    }
                }
            } else {
                show_error('No camera configuration templates found');
            }
        }
    } else {
        show_error($usageNotice);
    }
} else {
    show_error($usageNotice);
}