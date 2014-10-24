<?php

require_once(__DIR__ . '/init.php');

class QuickInstaller
{
	private $options;
	private $quickPath;
	private $cocosPath;
    private $cocosTemplatePath;
	private $defaultTemplatePath;
    private $quickTemplatePath;

    function __construct($quickPath, $cocosPath, $options)
    {
    	$this->options = $options;
        $this->quickPath = $quickPath;
        $this->cocosPath = $cocosPath;
        $this->cocosTemplatePath = $cocosPath . "/templates";
        $this->defaultTemplatePath = $this->cocosTemplatePath . "/lua-template-quick";
        $this->quickTemplatePath = $this->quickPath . "/quick/templates/lua-template-quick";
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

    private function copyFile($src, $dest)
    {
        printf("create file \"%s\" ... ", $dest);
        $destinationDir = pathinfo($dest, PATHINFO_DIRNAME);

        if (!is_dir($destinationDir))
        {
            mkdir($destinationDir, 0777, true);
        }
        if (!is_dir($destinationDir))
        {
            printf("ERROR: mkdir failure\n");
            return false;
        }

        $contents = file_get_contents($src);
        if ($contents == false)
        {
            printf("ERROR: file_get_contents failure\n");
            return false;
        }
        $stat = stat($src);

        // foreach ($this->vars as $key => $value)
        // {
        //     $contents = str_replace($key, $value, $contents);
        // }

        if (file_put_contents($dest, $contents) == false)
        {
            printf("ERROR: file_put_contents failure\n");
            return false;
        }
        chmod($dest, $stat['mode']);

        printf("OK\n");
        return true;
    }

    private function copyDir($srcPath, $dstPath)
    {
        $files = array();
        findFiles($srcPath, $files);
        foreach ($files as $src) 
        {
            $dest = str_replace($srcPath, $dstPath, $src);
            $this->copyFile($src, $dest);
        }
    }

    private function cleanupTemplate()
    {
        $this->cleanDir($this->defaultTemplatePath . '/res');
        $this->cleanDir($this->defaultTemplatePath . '/src');
    }

    private function copyTemplate()
    {
        $srcPath = $this->quickTemplatePath;
        $dstPath = $this->defaultTemplatePath;
        $this->copyDir($srcPath, $dstPath);
    }

    private function copyFilesToTemplate()
    {
    	$files = $this->options["FilesCopyToTemplate"];
    	foreach ($files as $key => $file) 
    	{
    		$src = $this->quickPath . $file[2] . '/' . $file[0];
    		$dest = $this->defaultTemplatePath . $file[3] . '/';
    		if ($file[1]) 
    		{
    			$dest = $dest . $file[1];
    		}
    		else
    		{
    			$dest = $dest . $file[0];
    		}
    		$this->copyFile($src, $dest);
    	}

    	$pathes = $this->options["PathesCopyToTemplate"];
    	foreach ($pathes as $path)
    	{
    		$srcPath = $this->quickPath . $path[0];
	        $dstPath = $this->defaultTemplatePath . $path[1];
	        $files = array();
	        findFiles($srcPath, $files);
	        foreach ($files as $src) 
	        {
	    		$dest = str_replace($srcPath, $dstPath, $src);
	    		$this->copyFile($src, $dest);
	        }
    	}
    }

    private function modifyFiles()
    {
        $files = $this->options["FilesNeedModify"];
        foreach ($files as $file) 
        {
            $src = $this->cocosPath . $file[0];
            printf("modify file \"%s\" ... ", $src);
            $contents = file_get_contents($src);
            if ($contents == false)
            {
                printf("ERROR: file_get_contents failure\n");
                return false;
            }
            // $stat = stat($src);

            $contents = str_replace($file[1], $file[2], $contents);

            if (file_put_contents($src, $contents) == false)
            {
                printf("ERROR: file_put_contents failure\n");
                return false;
            }
            // chmod($dest, $stat['mode']);

            printf("OK\n");
        }

        return true;
    }

    private function replaceFiles()
    {
        $files = $this->options["FilesNeedReplace"];
        foreach ($files as $file) 
        {
            $src = $this->quickPath . "/quick/lib/hotfix/" . $file[0];
            $dst = $this->cocosPath . $file[1];
            $this->copyFile($src, $dst);

            printf("OK\n");
        }

        return true;
    }

    private function setEnvFiles()
    {
        file_put_contents($this->quickPath . "/.COCOS_ROOT_PATH", $this->cocosPath);
        $quickBinPath = $this->quickPath . "/quick/bin";

        $sh_str = "#!/bin/bash\n";
        $bat_str = "@echo off\n";

        // $tmpstr = $this->quickPath;
        // $sh_str = $sh_str . "export QUICK_V3_ROOT=" . $tmpstr . "\n";
        // $bat_str = $bat_str . "set QUICK_V3_ROOT=" . $tmpstr . "\n";

        $tmpstr = $_ENV['COCOS_CONSOLE_ROOT'];
        if ($tmpstr) 
        {
            $sh_str = $sh_str . "export COCOS_CONSOLE_ROOT=" . $tmpstr . "\n";
            $bat_str = $bat_str . "set COCOS_CONSOLE_ROOT=" . $tmpstr . "\n";
        }
        $tmpstr = $_ENV['ANDROID_SDK_ROOT'];
        if ($tmpstr) 
        {
            $sh_str = $sh_str . "export ANDROID_SDK_ROOT=" . $tmpstr . "\n";
            $bat_str = $bat_str . "set ANDROID_SDK_ROOT=" . $tmpstr . "\n";
        }
        $tmpstr = $_ENV['NDK_ROOT'];
        if ($tmpstr) 
        {
            $sh_str = $sh_str . "export NDK_ROOT=" . $tmpstr . "\n";
            $bat_str = $bat_str . "set NDK_ROOT=" . $tmpstr . "\n";
        }
        $tmpstr = $_ENV['ANT_ROOT'];
        if ($tmpstr) 
        {
            $sh_str = $sh_str . "export ANT_ROOT=" . $tmpstr . "\n";
            $bat_str = $bat_str . "set ANT_ROOT=" . $tmpstr . "\n";
        }

        $fileSetEnv = $quickBinPath . "/_setenv.sh";
        file_put_contents($fileSetEnv, $sh_str);
        chmod($fileSetEnv, 0755);
        $fileSetEnv = $quickBinPath . "/_setenv.bat";
        file_put_contents($fileSetEnv, $bat_str);
        chmod($fileSetEnv, 0755);
    }

    function run()
    {
        $this->setEnvFiles();
    	// $this->cleanupTemplate();
        $this->copyTemplate();
    	$this->copyFilesToTemplate();
        $this->modifyFiles();
        $this->replaceFiles();
    	return 0;
    }

}