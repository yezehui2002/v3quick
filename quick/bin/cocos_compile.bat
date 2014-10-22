@echo off
set DIR=%~dp0
call %DIR%_setenv.bat
%DIR%win32\php.exe "%DIR%lib\cocos_compile.php" %*