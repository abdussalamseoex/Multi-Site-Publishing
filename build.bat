@echo off
echo Building cPanel Installable ZIP...
echo -----------------------------------

:: Create a temporary build directory
mkdir build_temp
xcopy . build_temp /E /C /I /Q /H /R /Y /EXCLUDE:exclude.txt

:: Create excludes list
echo node_modules\> exclude.txt
echo .git\>> exclude.txt
echo .env>> exclude.txt
echo build_temp\>> exclude.txt
echo release.zip>> exclude.txt
echo .gemini\>> exclude.txt

echo Running composer install...
cd build_temp
call composer install --no-dev --optimize-autoloader

cd ..
echo Zipping files for production...
powershell Compress-Archive -Path build_temp\* -DestinationPath release.zip -Force

echo Cleaning up...
rmdir /S /Q build_temp
del exclude.txt

echo -----------------------------------
echo Build complete: release.zip
pause
