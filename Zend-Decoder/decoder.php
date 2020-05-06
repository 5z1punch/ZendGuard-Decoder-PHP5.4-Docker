<?php
ini_set('memory_limit', '-1');
$srcdir = dirname(__FILE__);
require_once("$srcdir/Decompiler3.class.php");

$rii = new RecursiveIteratorIterator(new RecursiveDirectoryIterator("/usr/src/input"));
foreach ($rii as $file) {
    $pathName = $file->getPathname();
    // echo $pathName."\n";
    $outputPath = preg_replace('/input/','/output/',$pathName,1);
    if ($file->isDir()){
        if ($file->getFilename()!="." && $file->getFilename()!=".." && !file_exists($outputPath)) {
            mkdir($outputPath, 0777, true);
        }
    }
    else{
        $outputDir = preg_replace('/input/','/output/',$file->getPath(),1);
        if(!file_exists($outputDir)){
            mkdir($outputDir, 0777, true);
        }
        if($file->getExtension()=="php"){
            try{
                $dc = new Decompiler(array("php"));
                $dc->decompileFile($pathName);
                file_put_contents($outputPath, $dc->output());
                unset($dc);
            }
            catch (Exception $e) {
                echo "Error: ".$pathName." -> ".$e->getMessage()."\n";
            }
        }
        else{
            copy($pathName, $outputPath);
        }
    }
}

