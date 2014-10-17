<?php

require_once(__DIR__ . '/init.php');

class QuickInstaller
{
	private $quickPath;
	private $cocosPath;
	private $defaultTemplatePath;

    function __construct($quickPath, $cocosPath)
    {
        $this->quickPath = $quickPath;
        $this->cocosPath = $cocosPath;
        $this->defaultTemplatePath = $cocosPath . "/templates/lua-template-default";
    }

    private function cleanDir($path)
    {
        $files = array();
        findFiles($path, $files);

        foreach ($files as $key => $file) 
        {
        	echo "remove $file\n";
        	unlink($file);
        }
    }

    private function cleanupTemplate()
    {
        $this->cleanDir($this->defaultTemplatePath . '/res');
        $this->cleanDir($this->defaultTemplatePath . '/src');
    }

    function run()
    {
    	$this->cleanupTemplate();
    	return 0;
    }

}