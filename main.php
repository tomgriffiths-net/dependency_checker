<?php
class dependency_checker{
    public static function command($line):void{
        echo json_encode(self::getFileDependencies($line), JSON_PRETTY_PRINT) . "\n";
        $onlineInfo = pkgmgr::getPackageInfo($line,true);
        if(isset($onlineInfo['latest_version'])){
            echo "Package LastVersion: " . $onlineInfo['latest_version'] . "\n";
        }
    }
    //public static function init():void{}
    public static function getFileDependencies(string $localPackageId):array{
        if(pkgmgr::validatePackageId($localPackageId)){
            $file = 'packages/' . $localPackageId . '/main.php';
        }
        else{
            return array();
        }
        $text = file_get_contents($file);
        $offset = 0;
        $dependencies = array();
        $preloadedPackages = array("self","cli","cmd","commandline_list","data_types","downloader","extensions","files","json","time","timetest","txtrw","user_input","pkgmgr");
        while(true){
            $pos = strpos($text,"::",$offset);
            if($pos === false){
                break;
            }
            $pos2 = 1;
            while(true){
                $dependency = substr($text,$pos - $pos2,$pos2);
                if(preg_match("/^[a-zA-Z0-9_]+$/", $dependency) === 1){
                    $pos2++;
                }
                else{
                    break;
                }
            }
            $dependency = trim(substr($dependency,1));
            if(!in_array($dependency,$preloadedPackages) && !isset($dependencies[$dependency]) && $dependency !== $localPackageId){
                echo 'Querying ' . $dependency . "\n";
                $onlineInfo = pkgmgr::getPackageInfo($dependency,true);
                if(isset($onlineInfo['latest_version'])){
                    $leftfromdependency = 1;
                    $makedependency = true;
                    while(true){
                        $firsttwochars = substr($text,$pos - $pos2 - $leftfromdependency,2);
                        if($firsttwochars === "//"){
                            $makedependency = false;
                            break;
                        }
                        elseif(strpos($firsttwochars,"\n") !== false){
                            break;
                        }
                        else{
                            $leftfromdependency++;
                        }
                    }
                    
                    if($makedependency){
        
                        $dependencies[$dependency] = $onlineInfo['latest_version'];
                    }
                }
                
            }
            $offset = $pos+2;
        }
        ksort($dependencies);
        return $dependencies;
    }
}